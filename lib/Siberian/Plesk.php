<?php

/**
 * Class Siberian_Plesk
 */

class Siberian_Plesk {

    /**
     * @var mixed|null
     */
    public $logger = null;

    /**
     * @var array
     */
    protected $config = array(
        "host" => "127.0.0.1",
        "username" => "admin",
        "password" => "changeme",
    );

    /**
     * Siberian_Plesk constructor.
     */
    public function __construct() {
        $this->logger = Zend_Registry::get("logger");
        $plesk_api = Api_Model_Key::findKeysFor("plesk");

        $this->config["host"]       = $plesk_api->getHost();
        $this->config["username"]   = $plesk_api->getUser();
        $this->config["password"]   = $plesk_api->getPassword();
    }

    /**
     * @param $ssl_certificate
     */
    public function updateCertificate($ssl_certificate) {
        $cert_name = sprintf("%s-%s", "siberian_letsencrypt", $ssl_certificate->getHostname());

        $params_delete = array(
            "webspace" => $ssl_certificate->getHostname(),
            "cert-name" => $cert_name,
        );

        /** First try to remove an existing one */
        try {
            $request = new Plesk\SSL\DeleteCertificate($this->config, $params_delete);
            $info = $request->process();

            $this->logger->info(sprintf("[Siberian_Plesk] %s", $request->error));
            if(strpos($request->error, "Permission denied.") !== false) {
                // re throw the exception
                throw new Exception(__("Permission, denied from Plesk, please use the admin account."));
            }
            $this->logger->info(sprintf("[Siberian_Plesk] %s", $request->xml_response));
            $this->logger->info(sprintf("[Siberian_Plesk] %s", print_r($info, true)));
            $this->logger->info(sprintf("[Siberian_Plesk] %s", "Certificate cleaned-up"));
        } catch(Exception $e) {
            $this->logger->info(sprintf("[Siberian_Plesk] %s", $e->getMessage()));
            throw new Exception(__("Permission, denied from Plesk, please use the admin account."));
        }

        $params_install = array(
            "name"          => $cert_name,
            "webspace"      => $ssl_certificate->getHostname(),
            "csr"           => file_get_contents($ssl_certificate->getLast()),
            "cert"          => file_get_contents($ssl_certificate->getCertificate()),
            "pvt"           => file_get_contents($ssl_certificate->getPrivate()),
            "ip-address"    => gethostbyname($ssl_certificate->getHostname()),
        );

        try {
            $request = new Plesk\SSL\InstallCertificate($this->config, $params_install);
            $info = $request->process();

            $this->logger->info(sprintf("[Siberian_Plesk] %s", $request->error));
            if(strpos($request->error, "Permission denied.") !== false) {
                // re throw the exception
                throw new Exception("Permission, denied from Plesk, please use the admin account.");
            }
            $this->logger->info(sprintf("[Siberian_Plesk] %s", $request->xml_response));
            $this->logger->info(sprintf("[Siberian_Plesk] %s", print_r($info, true)));
            $this->logger->info(sprintf("[Siberian_Plesk] %s", "Certificate installed"));
        } catch(Exception $e) {
            $this->logger->info(sprintf("[Siberian_Plesk] Unable to install the certificate: %s", $e->getMessage()));
            $this->logger->info(sprintf("[Siberian_Plesk] %s", print_r($request->error, true)));
            throw new Exception(__("Permission, denied from Plesk, please use the admin account."));
        }


        // Ensure the subdomain/domain is correctly set-up
        try {
            $request = new Plesk\ListSubdomains($this->config);
            $results = $request->process();

            if(!empty($results) && is_array($results)) {
                foreach($results as $result) {
                    if($result["name"] == $ssl_certificate->getHostname()) {
                        $subdomain_id = $result["id"];
                        $params_update = array(
                            "id" => $subdomain_id,
                            "properties" => array(
                                "ssl" => true,
                            ),
                        );

                        // Ensure SSL is enabled default on the domain
                        try {
                            $update_subdomain = new Plesk\UpdateSubdomain($this->config, $params_update);
                            $info = $update_subdomain->process();

                            $this->logger->info(sprintf("[Siberian_Plesk] %s", $update_subdomain->error));
                            if(strpos($request->error, "Permission denied.") !== false) {
                                // re throw the exception
                                throw new Exception("Permission, denied from Plesk, please use the admin account.");
                            }
                            $this->logger->info(sprintf("[Siberian_Plesk] %s", $update_subdomain->xml_response));
                            $this->logger->info(sprintf("[Siberian_Plesk] %s", print_r($info, true)));
                            $this->logger->info(sprintf("[Siberian_Plesk] %s", "Domain updated"));
                        } catch(Exception $e) {
                            $this->logger->info(sprintf("[Siberian_Plesk] Unable to install/update the certificate: %s", $e->getMessage()));
                            $this->logger->info(sprintf("[Siberian_Plesk] %s", print_r($update_subdomain->error, true)));
                            return false;
                        }

                    }
                }
            }

        } catch(Exception $e) {
            // Do nothing
        }

        $this->logger->info(sprintf("[Siberian_Plesk] %s", "Done with success."));

        return true;
    }
}