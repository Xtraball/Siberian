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
        "webspace" => null,
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
        $this->config["webspace"]   = $cpanel_api->getWebspace();
    }

    /**
     * @param $ssl_certificate
     */
    public function updateCertificate($ssl_certificate) {
        $cpanel = new Siberian_Cpanel_Api($this->config["username"], $this->config["password"], $this->config["host"], true);

        // @note From here, server may reload, and then interrupt the connection
        // This is normal behavior, as it's reloading the SSL Certificate.

        $webspace = $ssl_certificate->getHostname();
        if(!empty($this->config["webspace"])) {
            $webspace = $this->config["webspace"];
        }

        $response = $cpanel->uapi->SSL->install_ssl(
            array(
                "domain"    => $webspace,
                "cert"      => file_get_contents($ssl_certificate->getCertificate()),
                "key"       => file_get_contents($ssl_certificate->getPrivate()),
                "cabundle"  => file_get_contents($ssl_certificate->getChain()),
            )
        );

        $result = (isset($response->status)) ? ($response->status) : false;

        if($result) {
            $this->logger->info(__("[Siberian_Cpanel] Updated cPanel SSL Certificate for %s", $webspace));
        } else {
            $this->logger->info(__("[Siberian_Cpanel] Unable to update cPanel SSL Certificate for %s", $webspace));
            throw new Exception(__("[Siberian_Cpanel] Unable to update cPanel SSL Certificate for %s", $webspace));
        }

        return true;

        // Please consider you can never have the acknowledgement
    }

}