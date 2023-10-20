<?php

use Siberian\ACME\Cert;
use Siberian\File;
use Siberian\Hook;
use Siberian\Json;
use Siberian\Version;
use Siberian\Request;

/**
 * Class Backoffice_Advanced_ConfigurationController
 */
class Backoffice_Advanced_ConfigurationController extends System_Controller_Backoffice_Default
{
    /**
     * @var array
     */
    public $_codes = [
        'disable_cron',
        'cron_interval',
        'environment',
        'update_channel',
        'cpanel_type',
        'letsencrypt_env',
        'send_statistics',
        'apk_build_type',
        'java_home',
        'java_options',
        'gradle_options',
        'session_lifetime',
    ];

    /**
     * @var string
     */
    public $_fake_password_key = '__not_safe_not_saved__';

    /**
     *
     */
    public function loadAction()
    {
        $data = [
            'title' => sprintf('%s > %s > %s',
                __('Settings'),
                __('Advanced'),
                __('Configuration')),
            'icon' => 'fa-toggle-on',
        ];

        $this->_sendJson($data);
    }

    /**
     *
     */
    public function findallAction()
    {
        $data = $this->_findconfig();

        $cpanel = Api_Model_Key::findKeysFor('cpanel');
        $plesk = Api_Model_Key::findKeysFor('plesk');
        $pleskcli = Api_Model_Key::findKeysFor('pleskcli');
        $vestacp = Api_Model_Key::findKeysFor('vestacp');
        $vestacpcli = Api_Model_Key::findKeysFor('vestacpcli');
        $directadmin = Api_Model_Key::findKeysFor('directadmin');

        $data['cpanel'] = $cpanel->getData();
        $data['plesk'] = $plesk->getData();
        $data['pleskcli'] = $pleskcli->getData();
        $data['vestacp'] = $vestacp->getData();
        $data['vestacpcli'] = $vestacpcli->getData();
        $data['directadmin'] = $directadmin->getData();

        $data['cpanel']['password'] = $this->_fake_password_key;
        $data['plesk']['password'] = $this->_fake_password_key;
        $data['vestacp']['password'] = $this->_fake_password_key;
        $data['directadmin']['password'] = $this->_fake_password_key;

        $ssl_certificate_model = new System_Model_SslCertificates();
        $certs = $ssl_certificate_model->findAll();

        $result = Siberian_Network::testSsl($this->getRequest()->getHttpHost(), true);
        $data['testssl'] = $result;

        $is_pe = Version::is('PE');
        if ($is_pe) {
            $whitelabel_model = new Whitelabel_Model_Editor();
        }

        // Environment value from config, not DB
        $data['environment']['value'] = __getConfig('environment');

        // Redirect HTTPS from DB (get real value from config, not DB)
        $data['redirect_https'] = [
            'label' => 'redirect_https',
            'code' => 'redirect_https',
            'value' => __getConfig('redirect_https') ? 'true' : 'false',
        ];

        $data['current_domain'] = $this->getRequest()->getHttpHost();
        $data['certificates'] = [];
        foreach ($certs as $cert) {
            $wls = [];
            if ($is_pe) {
                $whitelabels = $whitelabel_model->findAll(['is_active = ?', '1']);
                foreach ($whitelabels as $whitelabel) {
                    $wls[] = $whitelabel->getHost();
                }
            }

            $cert_data = [
                'id' => $cert->getId(),
                'whitelabels' => $wls,
                'domains' => Siberian_Json::decode($cert->getDomains()),
                'hostname' => $cert->getHostname(),
                'certificate' => $cert->getCertificate(),
                'chain' => $cert->getChain(),
                'fullchain' => $cert->getFullchain(),
                'last' => $cert->getLast(),
                'private' => $cert->getPrivate(),
                'public' => $cert->getPublic(),
                'source' => __($cert->getSource()),
                'created_at' => datetime_to_format($cert->getCreatedAt()),
                'updated_at' => datetime_to_format($cert->getUpdatedAt()),
                'show_info' => false,
                'more_info' => __('-')
            ];

            $cert_data = array_merge($cert_data, $cert->extractInformation());

            $data['certificates'][] = $cert_data;
        }

        $this->_sendJson($data);
    }

    /**
     *
     */
    public function submitreportAction()
    {
        try {
            $request = $this->getRequest();

            if ($params = Json::decode($request->getRawBody())) {

                ob_start();
                phpinfo();
                $phpinfo = ob_get_clean();

                $bug_report = [
                    'secret' => Core_Model_Secret::SECRET,
                    'data' => [
                        'host' => $request->getHttpHost(),
                        'type' => Version::TYPE,
                        'version' => Version::VERSION,
                        'canal' => __get('update_channel'),
                        'message' => base64_encode($params['message']),
                        'phpinfo' => base64_encode($phpinfo)
                    ]
                ];

                Request::post(sprintf("https://stats.xtraball.com/report.php?type=%s", Version::TYPE), $bug_report);

                $payload = [
                    'success' => true,
                    'message' => __('Thanks for your report.'),
                ];

            } else {
                throw new Siberian_Exception(__('Message is required.'));
            }

        } catch (Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * Save settings
     * &
     * cpanel/plesk credentials
     */
    public function saveAction()
    {
        if ($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {
                if (__getConfig('is_demo')) {
                    throw new Exception(__('This is a demo version, you cannot save these settings.'));
                }

                if ($data['session_lifetime']['value'] < 300) {
                    throw new Exception(__("Session lifetime can't be below 300 seconds / 5 minutes."));
                }

                $this->_save($data);

                $messageStart = __('Configuration successfully saved');
                $messages = [];

                $configFile = path('config.php');
                $configFileSample = path('config.sample.php');
                $configFileBackup = path('config.backup.php');
                $contents = file_get_contents($configFile);

                // Ensure file is up-to-date
                if (stripos($contents, 'redirect_https') === false) {
                    // Backup the file
                    rename($configFile, $configFileBackup);
                    // Copy the sample one
                    copy($configFileSample, $configFile);
                    chmod($configFile, 0777);

                    // Reload content
                    $contents = file_get_contents($configFile);
                }

                if (isset($data['environment']) && in_array($data['environment']['value'], ['production', 'development'])) {
                    if (is_writable($configFile)) {
                        $contents = preg_replace('/("|\')(development|production)("|\')/im', '"' . $data['environment']['value'] . '"', $contents);
                        File::putContents($configFile, $contents);
                    } else {
                        $messageStart = __('Configuration partially saved');
                        $messages[] = __('Error: unable to write Environment in config.php');
                    }
                }

                if (isset($data['redirect_https']) && in_array($data['redirect_https']['value'], ['true', 'flase'])) {
                    if (is_writable($configFile)) {
                        $contents = preg_replace('/(true|false)/im', $data['redirect_https']['value'], $contents);
                        File::putContents($configFile, $contents);
                    } else {
                        $messageStart = __('Configuration partially saved');
                        $messages[] = __('Error: unable to write Redirect HTTPS in config.php');
                    }
                }

                //Admin panel type & credentials
                $api_provider = new Api_Model_Provider();
                $api_key = new Api_Model_Key();

                $panel_type = $data['cpanel_type']['value'];
                $panel_api_provider = $api_provider->find($panel_type, 'code');
                if ($panel_api_provider->getId()) {
                    $keys = $api_key->findAll(['provider_id = ?' => $panel_api_provider->getId()]);
                    foreach ($keys as $key) {
                        switch ($key->getKey()) {
                            case 'ip':
                                $key->setValue($data[$panel_type]['ip'])->save();
                                break;
                            case 'host':
                                $key->setValue($data[$panel_type]['host'])->save();
                                break;
                            case 'user':
                                $key->setValue($data[$panel_type]['user'])->save();
                                break;
                            case 'webspace':
                                $key->setValue($data[$panel_type]['webspace'])->save();
                                break;
                            case 'password':
                                if ($data[$panel_type]['password'] != $this->_fake_password_key) {
                                    $key->setValue($data[$panel_type]['password'])->save();
                                }
                                break;
                        }
                    }
                }

                $data = [
                    'success' => true,
                    'message' => $messageStart . '<br />' . implode_polyfill('<br />', $messages),
                ];
            } catch (Exception $e) {
                $data = [
                    'error' => true,
                    'message' => $e->getMessage(),
                ];
            }

            $this->_sendJson($data);

        }

    }

    /**
     * Delete a certificate
     */
    public function removecertAction()
    {
        if ($cert_id = $this->getRequest()->getParam("cert_id")) {
            try {

                $ssl_certificate_model = new System_Model_SslCertificates();

                $cert = $ssl_certificate_model->find($cert_id);

                // Prevent some certificates from being un-intentionally removed!
                if (__getConfig('is_demo') && in_array(__getConfig('hostname'), $cert->getHostname())) {
                    throw new Siberian_Exception('This certificate is protected.');
                }

                $cert->delete();

                # Clean-up related CRON Alerts
                $backoffice_notification = new Backoffice_Model_Notification();
                $backoffice_notification::clear('System_Model_SslCertificates', $cert_id);

                $certs = $ssl_certificate_model->findAll();

                $data = [
                    'success' => true,
                    'message' => __('Successfully removed the certificate.'),
                ];

                $data['certificates'] = [];
                foreach ($certs as $cert) {
                    $data['certificates'][] = [
                        'id' => $cert->getId(),
                        'hostname' => $cert->getHostname(),
                        'certificate' => $cert->getCertificate(),
                        'chain' => $cert->getChain(),
                        'fullchain' => $cert->getFullchain(),
                        'last' => $cert->getLast(),
                        'private' => $cert->getPrivate(),
                        'public' => $cert->getPublic(),
                        'source' => __($cert->getSource()),
                        'created_at' => $cert->getFormattedCreatedAt(),
                        'updated_at' => $cert->getFormattedUpdatedAt(),
                        'show_info' => false,
                        'more_info' => __('-'),
                    ];
                }

            } catch (\Exception $e) {
                $data = [
                    'error' => true,
                    'message' => $e->getMessage(),
                ];
            }

            $this->_sendJson($data);
        }
    }

    public function createcertificateAction()
    {

        if ($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {
                if (empty($data['hostname']) ||
                    empty($data['cert_path']) ||
                    empty($data['private_path']) ||
                    empty($data['fullchain_path']) ||
                    empty($data['ca_path'])) {
                    throw new Exception('#824-01: ' . __('All the files are required.'));
                }

                $base = Core_Model_Directory::getBasePathTo('/var/apps/certificates/');

                # Test hostname
                $current_host = $this->getRequest()->getHttpHost();
                $current_ip = gethostbyname($current_host);
                $ip = gethostbyname($data['hostname']);
                $letsencrypt_env = System_Model_Config::getValueFor('letsencrypt_env');

                if ($current_ip != $ip) {
                    throw new Exception('#824-02: ' . __("The domain %s doesn't belong to you, or is not configured on your server.", $data['hostname']));
                }

                $ssl_certificate_model = new System_Model_SslCertificates();
                $ssl_cert = $ssl_certificate_model->find($data['hostname'], 'hostname');

                if ($ssl_cert->getId()) {
                    throw new Exception('#824-03: ' . __('An entry already exists for this hostname, remove it first if you need to change your certificates.'));
                }

                if ($data['upload'] === '1') {
                    # Move the TMP files, then save the certificate
                    $folder = $base . '/' . $data['hostname'];
                    if (!file_exists($folder)) {
                        if (!mkdir($folder, 0777, true) && !is_dir($folder)) {
                            throw new \RuntimeException(sprintf('Directory "%s" was not created', $folder));
                        }
                    }

                    rename($data['cert_path'], $folder . '/cert.pem');
                    rename($data['private_path'], $folder . '/private.pem');
                    rename($data['fullchain_path'], $folder . '/fullchain.pem');

                    $certContent = file_get_contents($folder . '/cert.pem');
                    $privateContent = file_get_contents($folder . '/private.pem');

                    # Read SAN
                    $cert_content = openssl_x509_parse(file_get_contents($folder . '/cert.pem'));
                    if (isset($cert_content['extensions']) && $cert_content['extensions']['subjectAltName']) {
                        $parts = explode(',', str_replace('DNS:', '', $cert_content['extensions']['subjectAltName']));
                        $certificate_hosts = [];
                        foreach ($parts as $part) {
                            $certificate_hosts[] = trim($part);
                        }
                    } else {
                        $certificate_hosts = [$data['hostname']];
                    }

                    // Checking cert/key couple
                    $goodPair = openssl_x509_check_private_key($certContent, $privateContent);
                    if ($goodPair === false) {
                        throw new Exception('#824-061: ' . __('The given private key does not match the certificate.'));
                    }

                    if (empty($certificate_hosts)) {
                        $certificate_hosts = [$data['hostname']];
                    }

                    $ssl_cert
                        ->setHostname($data['hostname'])
                        ->setCertificate($folder . '/cert.pem')
                        ->setPrivate($folder . '/private.pem')
                        ->setFullchain($folder . '/fullchain.pem')
                        ->setDomains(Json::encode($certificate_hosts))
                        ->setEnvironment($letsencrypt_env)
                        ->setSource(System_Model_SslCertificates::SOURCE_CUSTOMER);

                    if (is_readable($data['ca_path'])) {
                        rename($data['ca_path'], $folder . '/ca.pem');
                        $ssl_cert->setChain($folder . '/ca.pem');
                    }

                    $ssl_cert->save();

                } else {
                    # Test if all three paths are readable from Siberian !!!
                    if (!is_readable($data['cert_path']) ||
                        !is_readable($data['private_path']) ||
                        !is_readable($data['fullchain_path']) ||
                        !is_readable($data['ca_path'])) {
                        throw new Exception('#824-06: ' . __('One of the three given Certificates path is not readable, please make sure they have the good rights.'));
                    }

                    $certContent = file_get_contents($data['cert_path']);
                    $privateContent = file_get_contents($data['private_path']);

                    # Read SAN
                    $cert_content = openssl_x509_parse(file_get_contents($data['cert_path']));
                    if (isset($cert_content['extensions']) && $cert_content['extensions']['subjectAltName']) {
                        $parts = explode(',', str_replace('DNS:', '', $cert_content['extensions']['subjectAltName']));
                        $certificate_hosts = [];
                        foreach ($parts as $part) {
                            $certificate_hosts[] = trim($part);
                        }
                    } else {
                        $certificate_hosts = [$data['hostname']];
                    }

                    // Checking cert/key couple
                    $goodPair = openssl_x509_check_private_key($certContent, $privateContent);
                    if ($goodPair === false) {
                        throw new Exception('#824-061: ' . __('The given private key does not match the certificate.'));
                    }

                    if (empty($certificate_hosts)) {
                        $certificate_hosts = [$data['hostname']];
                    }

                    # Save the path as-is
                    $ssl_cert
                        ->setHostname($data['hostname'])
                        ->setCertificate($data['cert_path'])
                        ->setChain($data['ca_path'])
                        ->setFullchain($data['fullchain_path'])
                        ->setPrivate($data['private_path'])
                        ->setDomains(Json::encode($certificate_hosts))
                        ->setSource(System_Model_SslCertificates::SOURCE_CUSTOMER)
                        ->setEnvironment($letsencrypt_env)
                        ->save();
                }

                // Triggering a certificate change
                Hook::trigger('ssl.certificate.update', ['certificate' => $ssl_cert]);

                $certs = $ssl_certificate_model->findAll();

                $data = [
                    'success' => true,
                    'message' => __('Your certificates are saved.'),
                ];

                $data['certificates'] = [];
                foreach ($certs as $cert) {
                    $data['certificates'][] = [
                        'id' => $cert->getId(),
                        'hostname' => $cert->getHostname(),
                        'certificate' => $cert->getCertificate(),
                        'chain' => $cert->getChain(),
                        'fullchain' => $cert->getFullchain(),
                        'last' => $cert->getLast(),
                        'private' => $cert->getPrivate(),
                        'public' => $cert->getPublic(),
                        'source' => __($cert->getSource()),
                        'created_at' => $cert->getFormattedCreatedAt(),
                        'updated_at' => $cert->getFormattedUpdatedAt(),
                        'show_info' => false,
                        'more_info' => __('-'),
                    ];
                }

            } catch (Exception $e) {
                $data = [
                    'error' => true,
                    'message' => $e->getMessage(),
                ];
            }

            $this->_sendJson($data);

        }

    }

    /**
     * Test the current hostname SSL connection
     */
    public function testsslAction()
    {
        $result = Siberian_Network::testSsl($this->getRequest()->getHttpHost());

        $this->_sendJson($result);
    }

    /**
     * Warning about SSL Expiration in the backoffice dashboard
     */
    public function sslwarningAction()
    {
        $result = Siberian_Network::testSsl($this->getRequest()->getHttpHost());

        if (isset($result['success'])) {
            $rawData = $result['rawData'];
            $remain = $rawData['validTo_time_t'] - time();
            $remainInDays = floor($remain / 86400);
            $showWarning = (boolean)($remainInDays <= 15);
            $validUntil = datetime_to_format(date("Y-m-d H:i:s", $rawData['validTo_time_t']));
            $message = __("Your HTTPS/SSL Certificate is about to expire in <b>%s day%s</b>, don't forget to renew it on the following page <a href=\"%s\">configuration</a>.",
                $remainInDays,
                $remainInDays === 1 ? '' : 's',
                '/backoffice/advanced_configuration');

            $result['sslData'] = [
                'remainInDays' => $remainInDays,
                'showWarning' => $showWarning,
                'validUntil' => $validUntil,
                'message' => $message,
                'useHttps' => true,
            ];
        }

        $this->_sendJson($result);
    }

    /**
     * Simple action to check if host is ok (with json response)
     */
    public function checkhttpAction()
    {
        $data = [
            'success' => true,
            'message' => __('Success'),
        ];

        $this->_sendJson($data);
    }

    /**
     * Simple action to check if host is ok (with json response)
     */
    public function checksslAction()
    {
        $http_host = $this->getRequest()->getHttpHost();
        $request = new Siberian_Request();
        $result = $request->get('https://' . $http_host);
        if ($result) {
            $data = [
                'success' => true,
                'message' => __('Success'),
                'https_url' => 'https://' . $http_host,
                'http_url' => 'http://' . $http_host,
            ];
        } else {
            $data = [
                'error' => true,
                'message' => __('HTTPS not reachable'),
                'https_url' => 'https://' . $http_host,
                'http_url' => 'http://' . $http_host,
            ];
        }

        $this->_sendJson($data);
    }

    /**
     * This action may end unexpectedly because of the panel reloading the webserver
     * This is normal behavior
     */
    public function sendtopanelAction()
    {
        $request = $this->getRequest();
        $logger = Zend_Registry::get('logger');
        $panelType = __get('cpanel_type');
        $hostname = $request->getParam('hostname', $request->getHttpHost());

        $certificate = (new System_Model_SslCertificates())
            ->find($hostname, 'hostname');

        $uiPanels = [
            'plesk' => __('Plesk'),
            'cpanel' => __('WHM cPanel'),
            'vestacp' => __('VestaCP'),
            'vestacpcli' => __('VestaCPCli'),
            'directadmin' => __('DirectAdmin'),
            'self' => __('Self-managed'),
        ];

        // Sync cPanel - Plesk - VestaCP (beta) - DirectAdmin (beta)
        try {
            $message = __('Successfully saved Certificate to %s', $uiPanels[(string) $panelType]);
            switch ($panelType) {
                case 'plesk':
                    (new Siberian_Plesk())->uploadCertificate($certificate);

                    break;
                case 'pleskcli':
                    (new \Siberian\PleskCli())->installCertificate($certificate);

                    break;
                case 'cpanel':
                    (new Siberian_Cpanel())->updateCertificate($certificate);

                    break;
                case 'vestacp':
                    (new Siberian_VestaCP())->updateCertificate($certificate);

                    break;
                case 'vestacpcli':
                    (new Siberian\VestaCPCli())->installCertificate($certificate);

                    break;
                case 'directadmin':
                    (new Siberian_DirectAdmin())->updateCertificate($certificate);

                    break;
                case 'self':
                    $logger->info(__('Self-managed sync is not available for now.'));
                    $message = __('Self-managed sync is not available for now.');

                    break;
            }

            $payload = [
                'success' => true,
                'message' => $message,
            ];

        } catch (\Exception $e) {
            $logger->info('#824-50: ' . __('An error occured while saving certificate to %s.', $e->getMessage()));
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * Generate SSL from Let's Encrypt, then sync Admin Panel
     *
     * @throws Zend_Exception
     */
    public function generatesslAction()
    {
        $ui_panels = [
            'plesk' => __('Plesk'),
            'cpanel' => __('WHM cPanel'),
            'vestacp' => __('VestaCP'),
            'vestacpcli' => __('VestaCP Cli'),
            'directadmin' => __('DirectAdmin'),
            'self' => __('Self-managed'),
        ];

        $logger = Zend_Registry::get('logger');

        try {

            $letsencrypt_env = __get('letsencrypt_env');

            $letsencrypt_disabled = __get('letsencrypt_disabled');
            if (('production' === $letsencrypt_env) &&
                ($letsencrypt_disabled > time())) {
                $logger->info(__("[Let's Encrypt] cron renewal is disabled until %s due to rate limit hit, skipping.", date("d/m/Y H:i:s", $letsencrypt_disabled)));

                throw new Siberian_Exception(__('Certificate renewal is disabled until %s due to rate limit hit.', datetime_to_format(date("Y-M-d H:i:s", $letsencrypt_disabled))));

            } else {
                # Enabling back
                __set('letsencrypt_disabled', 0);
            }

            // Check panel type
            $panel_type = __get('cpanel_type');
            if ($panel_type === '-1') {
                throw new Siberian_Exception(__('You must select an Admin panel type before requesting a Certificate.'));
            }

            $request = $this->getRequest();
            $email = __get('support_email');
            $show_force = false;

            $root = path('/');
            $base = path('/var/apps/certificates/');
            $hostname = $request->getParam('hostname', $request->getHttpHost());

            // Build hostname list
            $hostnames = [$hostname];

            // Add whitelabels if PE
            $is_pe = Siberian_Version::is('PE');
            if ($is_pe) {
                $whitelabel_model = new Whitelabel_Model_Editor();
                $whitelabels = $whitelabel_model->findAll(['is_active = ?', '1']);

                foreach ($whitelabels as $_whitelabel) {
                    $whitelabel = trim($_whitelabel->getHost());

                    $endWithDot = preg_match("/.*\.$/im", $whitelabel);
                    $r = dns_get_record($whitelabel, DNS_CNAME);
                    $isCname = (isset($r[0], $r[0]['target']) && !empty($r) && ($r[0]['target'] === $hostname));
                    $isSelf = ($whitelabel === $hostname);

                    if (!$endWithDot && ($isCname || $isSelf) && $_whitelabel->getIsActive()) {
                        $logger->info(__('Adding %s to SAN.', $whitelabel));

                        $hostnames[] = $whitelabel;
                    }

                    if ($endWithDot) {
                        $logger->info(__('Removing domain %s, domain in dot notation is not supported.', $whitelabel));
                    }
                }
            }

            // Ensure folders have good rights
            exec("chmod -R 777 {$base}");
            exec("chmod -R 777 {$root}/.well-known");

            $acme = new Cert($letsencrypt_env !== 'staging');

            try {
                $acme->getAccount();
            } catch (\Exception $e) {
                $acme->register(true, $email);
            }

            $cert = (new System_Model_SslCertificates())->find($request->getHttpHost(), 'hostname');

            if (empty($hostnames)) {
                $hostnames = [$request->getHttpHost()];
            }

            // Remove temporary excluded domains from config.user.php
            $excludeDomainSsl = __getConfig('exclude_domain_ssl');
            if ($excludeDomainSsl && is_array($excludeDomainSsl)) {
                $hostnames = array_diff($hostnames, $excludeDomainSsl);
            }

            // Included domains from config.user.php
            $includeDomainSsl = __getConfig('include_domain_ssl');
            if ($includeDomainSsl && is_array($includeDomainSsl)) {
                foreach ($includeDomainSsl as $includeDomain) {
                    $hostnames[] = $includeDomain;
                }
            }

            // Truncate domains list to 100 (SAN is limited to 100 domains)
            $hostnames = array_slice(array_unique(array_filter($hostnames, 'strlen')), 0, 100);

            // Before generating certificate again, compare $hostnames OR expiration date
            $renew = false;
            if (!empty($hostnames) &&
                is_readable($cert->getCertificate())) {

                $cert_content = openssl_x509_parse(file_get_contents($cert->getCertificate()));

                if (isset($cert_content['extensions']) && $cert_content['extensions']['subjectAltName']) {
                    $certificate_hosts = explode(',', str_replace('DNS:', '', $cert_content['extensions']['subjectAltName']));
                    foreach ($hostnames as $_hostname) {
                        $_hostname = trim($_hostname);
                        if (!in_array($_hostname, $certificate_hosts, false)) {
                            $renew = true;
                            $logger->info(__("[Let's Encrypt] will add %s to SAN.", $_hostname));
                        }
                    }
                }

                // Or compare expiration date (will expire in 30 days or less)
                if (!$renew) {

                    //$thirty_days = 2592000;
                    $eight_days = 691200;
                    //$five_days = 432000;

                    $diff = $cert_content['validTo_time_t'] - time();
                    if ($diff < $eight_days) {
                        # Should renew
                        $renew = true;
                        $logger->info(__("[Let's Encrypt] will expire in %s days.", floor($diff / 86400)));
                    }
                }

            } else {
                $renew = true;
            }

            // staging against production, renew if environment is different
            if ($cert->getEnvironment() !== $letsencrypt_env) {
                $renew = true;
            }

            // Whether to renew or not the certificate
            if ($renew) {
                try {
                    $logger->info(__("[Let's Encrypt] renewing certificate."));

                    $docRoot = path('/');
                    $config = [
                        'challenge' => 'http-01',
                        'docroot' => $docRoot,
                    ];

                    $domainConfig = [];
                    foreach ($hostnames as $_hostName) {
                        $domainConfig[$_hostName] = $config;
                    }

                    $handler = static function($opts){
                        $fn = $opts['config']['docroot'] . $opts['key'];
                        if (!mkdir($concurrentDirectory = dirname($fn), 0777, true) && !is_dir($concurrentDirectory)) {
                            throw new \RuntimeException(sprintf('Directory "%s" was not created, please ensure you can write to the folder.', $concurrentDirectory));
                        }
                        file_put_contents($fn, $opts['value']);
                        return static function($opts){
                            unlink($opts['config']['docroot'] . $opts['key']);
                        };
                    };

                    // Ensure hostname folder exists
                    $hostnameDirectory = path("/var/apps/certificates/{$hostname}");
                    if (!mkdir($hostnameDirectory, 0777, true) && !is_dir($hostnameDirectory)) {
                        throw new \RuntimeException(sprintf('Directory "%s" was not created, please ensure you can write to the folder.', $hostnameDirectory));
                    }

                    $fullChainPath = path("/var/apps/certificates/{$hostname}/acme.fullchain.pem");
                    $certKey = $acme->generateRSAKey(2048);
                    $certKeyPath = path("/var/apps/certificates/{$hostname}/acme.privkey.pem");
                    file_put_contents($certKeyPath, $certKey);

                    $fullChain = $acme->getCertificateChain("file://{$certKeyPath}", $domainConfig, $handler);
                    file_put_contents($fullChainPath, $fullChain);

                    // Split fullchain
                    $fullChainContent = file_get_contents($fullChainPath);
                    $parts = explode("\n\n", $fullChainContent);
                    $certPath = path("/var/apps/certificates/{$hostname}/acme.cert.pem");
                    $chainPath = path("/var/apps/certificates/{$hostname}/acme.chain.pem");
                    file_put_contents($certPath, $parts[0]);
                    file_put_contents($chainPath, $parts[1]);

                    $result = true;

                } catch (\Exception $e) {
                    // Simply throws back the exception, we must know!
                    throw $e;
                }

            } else {
                // Sync cert/panel
                $result = true;
            }

            if ($result) {

                $cert
                    ->setErrorCount(0)
                    ->setStatus('enabled')
                    ->setHostname($hostname)
                    ->setSource(System_Model_SslCertificates::SOURCE_LETSENCRYPT)
                    ->setCertificate(sprintf('%s%s/%s', $base, $hostname, 'acme.cert.pem'))
                    ->setChain(sprintf('%s%s/%s', $base, $hostname, 'acme.chain.pem'))
                    ->setFullchain(sprintf('%s%s/%s', $base, $hostname, 'acme.fullchain.pem'))
                    ->setPrivate(sprintf('%s%s/%s', $base, $hostname, 'acme.privkey.pem'))
                    ->setEnvironment($letsencrypt_env)
                    ->setRenewDate(time_to_date(time() + 10, 'YYYY-MM-dd HH:mm:ss'))
                    ->setDomains(Siberian_Json::encode(array_values($hostnames), JSON_OBJECT_AS_ARRAY))
                    ->save();

                # On success -> clean-up related CRON Alerts
                $backoffice_notification = new Backoffice_Model_Notification();
                $backoffice_notification::clear('System_Model_SslCertificates', $cert->getId());

                // Triggering a certificate change
                Hook::trigger('ssl.certificate.update', ['certificate' => $cert]);

                $message = __('Certificate successfully generated. Please wait while %s is reloading configuration ...', $ui_panels[$panel_type]);

            } else {
                $cert
                    ->setErrorCount($cert->getErrorCount() + 1)
                    ->setErrorDate(time_to_date(time(), 'YYYY-MM-dd HH:mm:ss'))
                    ->setRenewDate(time_to_date(time() + 10, 'YYYY-MM-dd HH:mm:ss'))
                    ->save();

                if ($cert->getErrorCount() >= 3) {
                    $cert
                        ->setStatus('disabled')
                        ->save();
                    $message = '#824-90: ' . __('The certificate request failed 3 times, please check the certificate information, your panel credentials, and everything else.<br />If your certificate is valid, you can try to upload to panel only.');
                } else {

                    $message = '#824-07: ' . __('An unknown error occurred while issue-ing your certificate.');
                }
            }

            // Ensure folders have good rights
            exec("chmod -R 777 {$base}");
            exec("chmod -R 777 {$root}/.well-known");

            $data = [
                'success' => true,
                'show_force' => $show_force,
                'message' => $message,
                'all_messages' => [
                    'https_unreachable' => '#824-99: ' . __('HTTPS host is unreachable.'),
                    'polling_reload' => __('Waiting for %s to reload, this can take a while...', $panel_type),
                ],
            ];

        } catch (Exception $e) {

            if ((strpos($e->getMessage(), 'many currently pending authorizations') !== false) ||
                (strpos($e->getMessage(), 'many certificates already issued') !== false)) {
                # We hit the rate limit, disable for the next seven days
                $in_a_week = time() + 604800;
                __set('letsencrypt_disabled', $in_a_week);
            }

            $data = [
                'error' => true,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ];
        }

        $this->_sendJson($data);
    }

    /**
     *
     */
    public function uploadcertificateAction()
    {

        try {

            if (empty($_FILES) || empty($_FILES['file']['name'])) {
                throw new Exception(__('No file has been sent'));
            }

            $tmp_dir = Core_Model_Directory::getTmpDirectory(true);
            $adapter = new Zend_File_Transfer_Adapter_Http();
            $adapter->setDestination($tmp_dir);
            $code = $this->getRequest()->getParam('code');

            if ($adapter->receive()) {
                $file = $adapter->getFileInfo();

                $uuid = uniqid('true_', '');
                $new_name = "{$uuid}.pem";
                rename($file['file']['tmp_name'], $tmp_dir . '/' . $new_name);

                $data = [
                    'success' => true,
                    'code' => $code,
                    'tmp_name' => $file['file']['name'],
                    'tmp_path' => $tmp_dir . '/' . $new_name,
                ];
            } else {
                $messages = $adapter->getMessages();
                if (!empty($messages)) {
                    $message = implode_polyfill("\n", $messages);
                } else {
                    $message = __('An error occurred during the process. Please try again later.');
                }

                throw new Exception($message);
            }
        } catch (Exception $e) {
            $data = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($data);
    }

    /**
     *
     */
    public function downloadcertAction()
    {
        $request = $this->getRequest();
        $cert_id = $request->getParam('cert_id');
        $type = $request->getParam('type');

        $ssl_certificate_model = new System_Model_SslCertificates();
        $cert = $ssl_certificate_model->find($cert_id);

        if ($cert) {
            switch ($type) {
                case 'csr':
                    $name = sprintf('%s-%s', $cert->getHostname(), 'last.csr');
                    $path = $cert->getLast();
                    break;
                case 'cert':
                    $name = sprintf('%s-%s', $cert->getHostname(), 'certificate.pem');
                    $path = $cert->getCertificate();
                    break;
                case 'ca':
                    $name = sprintf('%s-%s', $cert->getHostname(), 'ca.pem');
                    $path = $cert->getChain();
                    break;
                case 'private':
                    $name = sprintf('%s-%s', $cert->getHostname(), 'private.pem');
                    $path = $cert->getPrivate();
                    break;
                default:
                    exit('no file.');
            }

            $this->_download($path, $name, 'application/octet-stream');
        }
    }

}
