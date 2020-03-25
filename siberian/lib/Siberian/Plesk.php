<?php

use Plesk\ListSites;
use Plesk\UpdateSite;
use Plesk\UpdateIPAddress;
use Plesk\SSL\DeleteCertificate;
use Plesk\SSL\InstallCertificate;

use System_Model_SslCertificates as SslCertificates;

use Siberian\Version;
use Siberian\Exception;

/**
 * Class Siberian_Plesk
 */

class Siberian_Plesk
{
    /**
     * @var mixed|null
     */
    public $logger = null;

    /**
     * @var array
     */
    public $config = [
        'host' => '127.0.0.1',
        'username' => 'admin',
        'password' => 'changeme',
        'webspace' => null,
    ];

    /**
     * Siberian_Plesk constructor.
     */
    public function __construct()
    {
        $this->logger = Zend_Registry::get('logger');
        $plesk_api = Api_Model_Key::findKeysFor('plesk');

        $this->config['host'] = $plesk_api->getHost();
        $this->config['username'] = $plesk_api->getUser();
        $this->config['password'] = $plesk_api->getPassword();
        $this->config['webspace'] = $plesk_api->getWebspace();
        $this->config['name'] = $plesk_api->getWebspace();
    }

    /**
     * @param $certificate
     * @return bool
     * @throws Exception
     */
    public function uploadCertificate($certificate): bool
    {
        try {
            $certname = 'siberiancms_letsencrypt-' . $this->config['webspace'];
            $ipAddress = gethostbyname($this->config['webspace']);

            $listSites = new ListSites($this->config, [
                'name' => $this->config['webspace']
            ]);
            $resultSites = $listSites->process();

            // We keep only the first site matching!
            $actualSite = $resultSites[0];
            $actualSiteId = $actualSite['id'];

            if (empty($actualSiteId)) {
                throw new Exception(p__('backoffice',
                    "The webspace '%s' doesn't exists.", $this->config['webspace']));
            }

            // Now we remove the attached certificate!
            $updateSite = new UpdateSite($this->config,
                [
                    'id' => $actualSiteId,
                    'properties' => [
                        'certificate_name' => ''
                    ]
                ]);
            $resultUpdate = $updateSite->process();
            if ($resultUpdate !== true) {
                throw new Exception(p__('backoffice',
                    "The webspace '%s' update failed.", $this->config['webspace']));
            }

            // We set the IP cert to default
            // For PE only
            if (Version::is('PE')) {
                // Now we remove the attached certificate!
                $updateSite = new UpdateIPAddress($this->config,
                    [
                        'ip_address' => $ipAddress,
                        'certificate_name' => 'default certificate'
                    ]);
                $resultUpdate = $updateSite->process();
            }

            // Now we delete the certificate
            $deleteCertificate = new DeleteCertificate($this->config,
                [
                    'webspace' => $this->config['webspace'],
                    'cert-name' => $certname
                ]);

            // Just in case there is multiple dead certificates with the same name!
            $loopBreaker = 0;
            $resultDeleteCertificate = true;
            while ($resultDeleteCertificate === true) {
                $resultDeleteCertificate = $deleteCertificate->process();
                $loopBreaker++;
                if ($loopBreaker > 10) {
                    throw new Exception(p__('backoffice',
                        'We are unable to delete any previous certificate, please check in your Plesk panel and remove all the siberiancms certificates manually!'));
                }
            }

            // Plesk requires a default CSR.
            $dn = [
                'countryName' => 'GB',
                'stateOrProvinceName' => 'Nowhere',
                'localityName' => 'Island',
                'organizationName' => 'MobileAppsCompany',
                'organizationalUnitName' => 'MobileAppsCompany Team',
                'commonName' => 'MobileAppsCompany',
                'emailAddress' => 'mobileappscompany@sample.com'
            ];

            $privkey = openssl_pkey_get_private(file_get_contents($certificate->getPrivate()));
            $csr = openssl_csr_new($dn, $privkey, ['digest_alg' => 'sha256']);
            openssl_csr_export($csr, $csrout);

            $paramsInstall = [
                'name' => $certname,
                'webspace' => $this->config['webspace'],
                'csr' => $csrout,
                'cert' => file_get_contents($certificate->getCertificate()),
                'pvt' => file_get_contents($certificate->getPrivate()),
                'ca' => file_get_contents($certificate->getChain()),
            ];

            // For PE only
            if (Version::is('PE')) {
                $paramsInstall['ip-address'] = $ipAddress;
            }

            $installRequest = new InstallCertificate($this->config, $paramsInstall);
            $installRequest->process();

            // Now we re-attache the uploaded certificate!
            $updateSiteLast = new UpdateSite($this->config,
                [
                    'id' => $actualSiteId,
                    'properties' => [
                        'certificate_name' => $certname
                    ]
                ]);
            $resultUpdateLast = $updateSiteLast->process();
            if ($resultUpdateLast !== true) {
                throw new Exception(p__('backoffice',
                    'We are unable to install the certificate on your webspace!'));
            }

        } catch (\Plesk\ApiRequestException $e) {
            throw new Exception(p__('backoffice', $e->getMessage()));
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }

        return true;
    }

}