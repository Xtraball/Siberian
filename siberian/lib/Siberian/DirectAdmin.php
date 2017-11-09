<?php

/**
 * Class Siberian_DirectAdmin
 */

class Siberian_DirectAdmin {

    /**
     * @var mixed|null
     */
    public $logger = null;

    /**
     * @var Siberian_DirectAdmin_HTTPSocket
     */
    public $socket = null;

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

        $this->socket = new Siberian_DirectAdmin_HTTPSocket();

        $directadmin_api = Api_Model_Key::findKeysFor("directadmin");

        $parts = parse_url($directadmin_api->getHost());

        $this->config["host"]       = $directadmin_api->getHost();
        $this->config["username"]   = $directadmin_api->getUser();
        $this->config["password"]   = $directadmin_api->getPassword();
        $this->config["webspace"]   = $directadmin_api->getWebspace();

        $scheme = "https";
        if(isset($parts["host"]) && $parts["scheme"] == "ssl") {
            $scheme = "ssl";
        }

        $port = 2222;
        if(isset($parts["port"])) {
            $port = $parts["port"];
        }

        $host = $directadmin_api->getHost();
        if (isset($parts["host"])) {
            $host = $parts["host"];
        } elseif(isset($parts["path"])) {
            $host = $parts["path"];
        }

        $this->socket->connect($scheme."://".$host, $port);
    }

    /**
     * @param $ssl_certificate
     */
    public function updateCertificate($ssl_certificate) {

        $certificate = file_get_contents($ssl_certificate->getPrivate()).
            "\n".file_get_contents($ssl_certificate->getCertificate()).
            "\n".file_get_contents($ssl_certificate->getChain());

        $this->logger->info($certificate);

        // @note From here, server may reload, and then interrupt the connection
        // This is normal behavior, as it's reloading the SSL Certificate.

        $webspace = $ssl_certificate->getHostname();
        if(!empty($this->config["webspace"])) {
            $webspace = $this->config["webspace"];
        }

        $this->socket->set_login($this->config["username"], $this->config["password"]);
        $this->socket->method = "POST";
        $this->socket->query("/CMD_API_SSL", array(
            "domain"        => $webspace,
            "action"        => "save",
            "type"          => "paste",
            "certificate"   => $certificate
        ));
        $result = $this->socket->fetch_parsed_body();

        $this->logger->info(__("[Siberian_DirectAdmin] Updated DirectAdmin SSL Certificate for %s, %s", $webspace, print_r($result, true)));

        return true;

        // Please consider you can never have the acknowledgement
    }

}