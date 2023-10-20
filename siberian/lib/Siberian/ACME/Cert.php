<?php

namespace Siberian\ACME;

/**
 * Class Cert
 * @package Siberian\ACME
 */
class Cert extends v2
{

    /**
     * Cert constructor.
     * @param bool $live
     * @throws \Exception
     */
    public function __construct($live = true)
    {
        parent::__construct($live);

        $this->initAccountKey();
    }

    /**
     * @param bool $termsOfServiceAgreed
     * @param array $contacts
     * @return mixed
     * @throws Exception
     */
    public function register($termsOfServiceAgreed = true, $contacts = [])
    {
        $this->log('Registering account');

        $ret = $this->request('newAccount', [
            'termsOfServiceAgreed' => (bool) $termsOfServiceAgreed,
            'contact' => $this->make_contacts_array($contacts),
        ]);
        $this->log($ret['code'] == 201 ? 'Account registered' : 'Account already registered');
        return $ret['body'];
    }

    /**
     * @throws \Exception
     */
    public function initAccountKey () {
        // Staging options
        if ($this->mode === "live") {
            $accountKey = path("/var/apps/certificates/_account/acme.account.pem");
            $folderPath = path("/var/apps/certificates/_account/");
            if (!is_dir($folderPath)) {
                mkdir($folderPath, 0777, true);
            }
        } else {
            $accountKey = path("/var/apps/certificates/_account-staging/acme.account.pem");
            $folderPath = path("/var/apps/certificates/_account-staging/");
            if (!is_dir($folderPath)) {
                mkdir($folderPath, 0777, true);
            }
        }

        // Account key file
        if (!is_file($accountKey)) {
            $key = $this->generateRSAKey(2048);
            file_put_contents($accountKey, $key);
        }

        try {
            $this->loadAccountKey("file://$accountKey");
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * @param array $contacts
     * @return mixed
     * @throws Exception
     */
    public function update($contacts = [])
    {
        $this->log('Updating account');
        $ret = $this->request($this->getAccountID(), [
            'contact' => $this->make_contacts_array($contacts),
        ]);
        $this->log('Account updated');
        return $ret['body'];
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getAccount()
    {
        $ret = parent::getAccount();
        return $ret['body'];
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function deactivateAccount()
    {
        $this->log('Deactivating account');
        $ret = $this->deactivate($this->getAccountID());
        $this->log('Account deactivated');
        return $ret;
    }

    /**
     * @param $url
     * @return mixed
     * @throws Exception
     */
    public function deactivate($url)
    {
        $this->log('Deactivating resource: ' . $url);
        $ret = $this->request($url, ['status' => 'deactivated']);
        $this->log('Resource deactivated');
        return $ret['body'];
    }

    /**
     * @param $new_account_key_pem
     * @return mixed
     * @throws Exception
     */
    public function keyChange($new_account_key_pem)
    { // account key roll-over
        $ac2 = new v2();
        $ac2->loadAccountKey($new_account_key_pem);
        $account = $this->getAccountID();
        $ac2->resources = $this->resources;

        $this->log('Account Key Roll-Over');

        $ret = $this->request('keyChange',
            $ac2->jws_encapsulate('keyChange', [
                'account' => $account,
                'oldKey' => $this->jwk_header['jwk'],
            ], true)
        );
        $this->log('Account Key Roll-Over successful');

        $this->loadAccountKey($new_account_key_pem);
        return $ret['body'];
    }

    /**
     * @param $pem
     * @throws Exception
     */
    public function revoke($pem)
    {
        if (false === ($res = openssl_x509_read($pem))) {
            throw new \Exception('Could not load certificate: ' . $pem . ' (' . $this->get_openssl_error() . ')');
        }
        if (false === (openssl_x509_export($res, $certificate))) {
            throw new \Exception('Could not export certificate: ' . $pem . ' (' . $this->get_openssl_error() . ')');
        }

        $this->log('Revoking certificate');
        $this->request('revokeCert', [
            'certificate' => $this->base64url($this->pem2der($certificate)),
        ]);
        $this->log('Certificate revoked');
    }

    /**
     * @param $pem
     * @param $domain_config
     * @param $callback
     * @return mixed
     * @throws Exception
     */
    public function getCertificateChain($pem, $domain_config, $callback)
    {
        $domain_config = array_change_key_case($domain_config, CASE_LOWER);
        $domains = array_keys($domain_config);

        // autodetect if Private Key or CSR is used
        if ($key = openssl_pkey_get_private($pem)) { // Private Key detected
            openssl_free_key($key);
            $this->log('Generating CSR');
            $csr = $this->generateCSR($pem, $domains);
        } elseif (openssl_csr_get_subject($pem)) { // CSR detected
            $this->log('Using provided CSR');
            $csr = $pem;
        } else {
            throw new \Exception('Could not load Private Key or CSR (' . $this->get_openssl_error() . '): ' . $pem);
        }

        $this->getAccountID(); // get account info upfront to avoid mixed up logging order

        // === Order ===
        $this->log('Creating Order');
        $ret = $this->request('newOrder', [
            'identifiers' => array_map(
                function ($domain) {
                    return ['type' => 'dns', 'value' => $domain];
                },
                $domains
            ),
        ]);
        $order = $ret['body'];
        $order_location = $ret['headers']['location'];
        $this->log('Order created: ' . $order_location);

        // === Authorization ===
        if ($order['status'] === 'ready') {
            $this->log('All authorizations already valid, skipping validation altogether');
        } else {
            $groups = [];
            $auth_count = count($order['authorizations']);

            foreach ($order['authorizations'] as $idx => $auth_url) {
                $this->log('Fetching authorization ' . ($idx + 1) . ' of ' . $auth_count);
                $ret = $this->request($auth_url, '');
                $authorization = $ret['body'];

                // wildcard authorization identifiers have no leading *.
                $domain = ( // get domain and add leading *. if wildcard is used
                    isset($authorization['wildcard']) &&
                    $authorization['wildcard'] ?
                        '*.' : ''
                    ) . $authorization['identifier']['value'];

                if ($authorization['status'] === 'valid') {
                    $this->log('Authorization of ' . $domain . ' already valid, skipping validation');
                    continue;
                }

                // groups are used to be able to set more than one TXT Record for one subdomain
                // when using dns-01 before firing the validation to avoid DNS caching problem
                $groups[$domain_config[$domain]['challenge'] .
                '|' .
                ltrim($domain, '*.')][$domain] = [$auth_url, $authorization];
            }

            // make sure dns-01 comes last to avoid DNS problems for other challenges
            krsort($groups);

            foreach ($groups as $group) {
                $pending_challenges = [];

                try { // make sure that pending challenges are cleaned up in case of failure
                    foreach ($group as $domain => $arr) {
                        list($auth_url, $authorization) = $arr;

                        $config = $domain_config[$domain];
                        $type = $config['challenge'];

                        $challenge = $this->parse_challenges($authorization, $type, $challenge_url);

                        $opts = [
                            'domain' => $domain,
                            'config' => $config,
                        ];
                        list($opts['key'], $opts['value']) = $challenge;

                        $this->log('Triggering challenge callback for ' . $domain . ' using ' . $type);
                        $remove_cb = $callback($opts);

                        $pending_challenges[] = [$remove_cb, $opts, $challenge_url, $auth_url];
                    }

                    foreach ($pending_challenges as $arr) {
                        list($remove_cb, $opts, $challenge_url, $auth_url) = $arr;
                        $this->log('Notifying server for validation of ' . $opts['domain']);
                        $this->request($challenge_url, new \StdClass);

                        $this->log('Waiting for server challenge validation');
                        sleep(1);

                        if (!$this->poll('pending', $auth_url, $body)) {
                            $this->log('Validation failed: ' . $opts['domain']);

                            $ret = array_values(array_filter($body['challenges'], function ($item) {
                                return isset($item['error']);
                            }));

                            $error = $ret[0]['error'];
                            throw new Exception($error['type'], 'Challenge validation failed: ' . $error['detail']);
                        } else {
                            $this->log('Validation successful: ' . $opts['domain']);
                        }
                    }

                } finally { // cleanup pending challenges
                    foreach ($pending_challenges as $arr) {
                        list($remove_cb, $opts) = $arr;
                        if ($remove_cb) {
                            $this->log('Triggering remove callback for ' . $opts['domain']);
                            $remove_cb($opts);
                        }
                    }
                }
            }
        }

        $this->log('Finalizing Order');

        $ret = $this->request($order['finalize'], [
            'csr' => $this->base64url($this->pem2der($csr)),
        ]);
        $ret = $ret['body'];

        if (isset($ret['certificate'])) {
            return $this->request_certificate($ret);
        }

        if ($this->poll('processing', $order_location, $ret)) {
            return $this->request_certificate($ret);
        }

        throw new \Exception('Order failed');
    }

    /**
     * @param $domain_key_pem
     * @param $domains
     * @return mixed
     * @throws \Exception
     */
    public function generateCSR($domain_key_pem, $domains)
    {
        if (false === ($domain_key = openssl_pkey_get_private($domain_key_pem))) {
            throw new \Exception('Could not load domain key: ' . $domain_key_pem . ' (' . $this->get_openssl_error() . ')');
        }

        $fn = $this->tmp_ssl_cnf($domains);
        $dn = ['commonName' => reset($domains)];
        $csr = openssl_csr_new($dn, $domain_key, [
            'config' => $fn,
            'req_extensions' => 'SAN',
            'digest_alg' => 'sha512',
        ]);
        unlink($fn);
        openssl_free_key($domain_key);

        if (false === $csr) {
            throw new \Exception('Could not generate CSR ! (' . $this->get_openssl_error() . ')');
        }
        if (false === openssl_csr_export($csr, $out)) {
            throw new \Exception('Could not export CSR ! (' . $this->get_openssl_error() . ')');
        }

        return $out;
    }

    /**
     * @param $opts
     * @return mixed
     * @throws \Exception
     */
    private function generateKey($opts)
    {
        $fn = $this->tmp_ssl_cnf();
        $config = ['config' => $fn] + $opts;
        if (false === ($key = openssl_pkey_new($config))) {
            throw new \Exception('Could not generate new private key ! (' . $this->get_openssl_error() . ')');
        }
        if (false === openssl_pkey_export($key, $pem, null, $config)) {
            throw new \Exception('Could not export private key ! (' . $this->get_openssl_error() . ')');
        }
        unlink($fn);
        openssl_free_key($key);
        return $pem;
    }

    /**
     * @param int $bits
     * @return mixed
     * @throws \Exception
     */
    public function generateRSAKey($bits = 2048)
    {
        return $this->generateKey([
            'private_key_bits' => $bits,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
    }

    /**
     * @param string $curve_name
     * @return mixed
     * @throws \Exception
     */
    public function generateECKey($curve_name = 'P-384')
    {
        if (version_compare(PHP_VERSION, '7.1.0') < 0) throw new \Exception('PHP >= 7.1.0 required for EC keys !');
        $map = ['P-256' => 'prime256v1', 'P-384' => 'secp384r1', 'P-521' => 'secp521r1'];
        if (isset($map[$curve_name])) $curve_name = $map[$curve_name];
        return $this->generateKey([
            'curve_name' => $curve_name,
            'private_key_type' => OPENSSL_KEYTYPE_EC,
        ]);
    }

    /**
     * @param $cert_pem
     * @return array|resource
     * @throws \Exception
     */
    public function parseCertificate($cert_pem)
    {
        if (false === ($ret = openssl_x509_read($cert_pem))) {
            throw new \Exception('Could not load certificate: ' . $cert_pem . ' (' . $this->get_openssl_error() . ')');
        }
        if (!is_array($ret = openssl_x509_parse($ret, true))) {
            throw new \Exception('Could not parse certificate (' . $this->get_openssl_error() . ')');
        }
        return $ret;
    }

    /**
     * @param $cert_pem
     * @return float|int
     * @throws \Exception
     */
    public function getRemainingDays($cert_pem)
    {
        $ret = $this->parseCertificate($cert_pem);
        return ($ret['validTo_time_t'] - time()) / 86400;
    }

    /**
     * @param $domain_key_pem
     * @param $domain
     * @param $token
     * @return mixed
     * @throws \Exception
     */
    public function generateALPNCertificate($domain_key_pem, $domain, $token)
    {
        $domains = [$domain];
        $csr = $this->generateCSR($domain_key_pem, $domains);

        $fn = $this->tmp_ssl_cnf($domains, '1.3.6.1.5.5.7.1.31=critical,DER:0420' . $token . "\n");
        $config = [
            'config' => $fn,
            'x509_extensions' => 'SAN',
            'digest_alg' => 'sha512',
        ];
        $cert = openssl_csr_sign($csr, null, $domain_key_pem, 1, $config);
        unlink($fn);
        if (false === $cert) {
            throw new \Exception('Could not generate self signed certificate ! (' . $this->get_openssl_error() . ')');
        }
        if (false === openssl_x509_export($cert, $out)) {
            throw new \Exception('Could not export self signed certificate ! (' . $this->get_openssl_error() . ')');
        }
        return $out;
    }

    /**
     * @param $authorization
     * @param $type
     * @param $url
     * @return array
     * @throws \Exception
     */
    private function parse_challenges($authorization, $type, &$url)
    {
        foreach ($authorization['challenges'] as $idx => $challenge) {
            if ($challenge['type'] != $type) continue;

            $url = $challenge['url'];

            switch ($challenge['type']) {
                case 'dns-01':
                    return [
                        '_acme-challenge.' . $authorization['identifier']['value'],
                        $this->base64url(hash('sha256', $this->keyAuthorization($challenge['token']), true)),
                    ];
                    break;
                case 'http-01':
                    return [
                        '/.well-known/acme-challenge/' . $challenge['token'],
                        $this->keyAuthorization($challenge['token']),
                    ];
                    break;
                case 'tls-alpn-01':
                    return [null, hash('sha256', $this->keyAuthorization($challenge['token']))];
                    break;
            }
        }
        throw new \Exception('Challenge type: "' . $type . '" not available');
    }

    /**
     * @param $initial
     * @param $type
     * @param $ret
     * @return bool
     * @throws Exception
     */
    private function poll($initial, $type, &$ret)
    {
        $max_tries = 8;
        for ($i = 0; $i < $max_tries; $i++) {
            $ret = $this->request($type);
            $ret = $ret['body'];
            if ($ret['status'] !== $initial) return $ret['status'] === 'valid';
            $s = pow(2, min($i, 6));
            if ($i !== $max_tries - 1) {
                $this->log('Retrying in ' . ($s) . 's');
                sleep($s);
            }
        }
        throw new \Exception('Aborted after ' . $max_tries . ' tries');
    }

    /**
     * @param $ret
     * @return mixed
     * @throws Exception
     */
    private function request_certificate($ret)
    {
        $this->log('Requesting certificate-chain');
        $ret = $this->request($ret['certificate'], '');
        if ($ret['headers']['content-type'] !== 'application/pem-certificate-chain') {
            throw new \Exception('Unexpected content-type: ' . $ret['headers']['content-type']);
        }
        $this->log('Certificate-chain retrieved');
        return $ret['body'];
    }

    /**
     * @param null $domains
     * @param string $extension
     * @return bool|string
     * @throws \Exception
     */
    private function tmp_ssl_cnf($domains = null, $extension = '')
    {
        $fn = path('/var/tmp/CNF_' . uniqid('CNF_', true));
        if (false === file_put_contents($fn,
                'HOME = .' . "\n" .
                'RANDFILE=$ENV::HOME/.rnd' . "\n" .
                '[v3_ca]' . "\n" .
                '[req]' . "\n" .
                'default_bits=2048' . "\n" .
                ($domains ?
                    'distinguished_name=req_distinguished_name' . "\n" .
                    '[req_distinguished_name]' . "\n" .
                    '[v3_req]' . "\n" .
                    '[SAN]' . "\n" .
                    'subjectAltName=' .
                    implode_polyfill(',', array_map(function ($domain) {
                        return 'DNS:' . $domain;
                    }, $domains)) . "\n"
                    :
                    ''
                ) . $extension
            )) {
            throw new \Exception('Failed to write tmp file: ' . $fn);
        }
        return $fn;
    }

    /**
     * @param $pem
     * @return bool|string
     */
    private function pem2der($pem)
    {
        return base64_decode(implode_polyfill('', array_slice(
            array_map('trim', explode("\n", trim($pem))), 1, -1
        )));
    }

    /**
     * @param $contacts
     * @return array
     */
    private function make_contacts_array($contacts)
    {
        if (!is_array($contacts)) {
            $contacts = $contacts ? [$contacts] : [];
        }
        return array_map(function ($contact) {
            return 'mailto:' . $contact;
        }, $contacts);
    }
}
