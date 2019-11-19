<?php

namespace Siberian;

use Siberian\VestaCP\Api as VestaCPApi;
use Zend_Registry;
use Api_Model_Key;

/**
 * Class VestaCP
 * @package Siberian
 *
 * @replay 4.16.1
 */
class VestaCP
{

    /**
     * @var Api
     */
    protected $api;

    /**
     * @var mixed|null
     */
    public $logger = null;

    /**
     * @var array
     */
    protected $config = [
        "host" => "",
        "username" => "",
        "password" => "",
        "webspace" => null,
    ];

    /**
     * VestaCP constructor.
     * @throws Exception
     * @throws \Zend_Exception
     */
    public function __construct()
    {
        $this->logger = Zend_Registry::get("logger");
        $vestacp_api = Api_Model_Key::findKeysFor("vestacp");

        $this->config["host"] = $vestacp_api->getHost();
        $this->config["username"] = $vestacp_api->getUser();
        $this->config["password"] = $vestacp_api->getPassword();
        $this->config["webspace"] = $vestacp_api->getWebspace();

        $this->api = new VestaCPApi(
            $this->config["host"], $this->config["username"], $this->config["password"], $this->config["webspace"]);
    }

    /**
     * @param $ssl_certificate
     * @return bool
     * @throws \Siberian\Exception
     */
    public function updateCertificate($ssl_certificate)
    {
        $this->api->updateDomainVesta($ssl_certificate);

        // Please consider you can never have the acknowledgement
        return true;
    }
}