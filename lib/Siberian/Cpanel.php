<?php

/**
 * Class Siberian_Cpanel
 */

class Siberian_Cpanel {

    /**
     * @var mixed|null
     */
    public $logger = null;

    /**
     * @var array
     */
    protected $config = array(
        "host" => "",
        "username" => "",
        "password" => "",
    );

    /**
     * Siberian_Plesk constructor.
     */
    public function __construct() {
        $this->logger = Zend_Registry::get("logger");
        $cpanel_api = Api_Model_Key::findKeysFor("cpanel");

        $this->config["host"]       = $cpanel_api->getHost();
        $this->config["username"]   = $cpanel_api->getUser();
        $this->config["password"]   = $cpanel_api->getPassword();
    }

    /**
     * @param $ssl_certificate
     */
    public function updateCertificate($ssl_certificate) {
        $cpanel = new Siberian_Cpanel_Api($this->config["username"], $this->config["password"], $this->config["host"], true);

        // @note From here, server may reload, and then interrupt the connection
        // This is normal behavior, as it's reloading the SSL Certificate.

        $response = $cpanel->uapi->SSL->install_ssl(
            array(
                "domain"    => $ssl_certificate->getHostname(),
                "cert"      => file_get_contents($ssl_certificate->getCertificate()),
                "key"       => file_get_contents($ssl_certificate->getPrivate()),
                "cabundle"  => file_get_contents($ssl_certificate->getChain()),
            )
        );

        $result = (isset($response->status)) ? ($response->status) : false;

        if($result) {
            $this->logger->info(__("[Siberian_Cpanel] Updated cPanel SSL Certificate for %s", $ssl_certificate->getHostname()));
        } else {
            $this->logger->info(__("[Siberian_Cpanel] Unable to update cPanel SSL Certificate for %s", $ssl_certificate->getHostname()));
            throw new Exception(__("[Siberian_Cpanel] Unable to update cPanel SSL Certificate for %s", $ssl_certificate->getHostname()));
        }

        return true;

        // Please consider you can never have the acknowledgement
    }

}