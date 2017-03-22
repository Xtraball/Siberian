<?php

class Siberian_VestaCP_Api {

    /**
     * @var Client
     */
    public $client;

    /**
     * @var \Symfony\Component\DomCrawler\Crawler
     */
    public $crawler;

    /**
     * Apps constructor.
     *
     * Login into dashboard
     *
     * @param $host
     * @param $password
     * @param $password
     * @param $webspace
     */
    public function __construct($host, $username, $password, $webspace) {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->webspace = $webspace;
        $this->client = new Siberian_VestaCP_Client();

        $this->login();
    }

    /**
     *
     */
    public function login() {
        /** For no reason, sometimes one method works, sometimes the other one */
        try {
            $this->crawler = $this->client->_request("GET", $this->host."/login/");

            $form = $this->crawler->selectButton("Log in")->form(array(
                "user" => $this->username,
                "password" => $this->password
            ));

            $this->client->submit($form);
        } catch(Exception $e) {
            $this->crawler = $this->client->_request("POST", $this->host."/login/", array(
                "user" => $this->username,
                "password" => $this->password
            ));
        }

        $this->crawler = $this->client->_request("GET", $this->host);
    }

    /**
     * @param $ssl_certificate
     */
    public function updateDomain($ssl_certificate) {

        $webspace = $ssl_certificate->getHostname();
        if(!empty($this->webspace)) {
            $webspace = $this->webspace;
        }

        $this->crawler = $this->client->_request("GET", $this->host."/edit/web/?domain=".$webspace);

        try {
            $form = $this->crawler->selectButton("Save")->form(array(
                "v_ssl" => "on",
                "v_ssl_home" => "same",
                "v_ssl_crt" => file_get_contents($ssl_certificate->getCertificate()),
                "v_ssl_key" => file_get_contents($ssl_certificate->getPrivate()),
                "v_ssl_ca" => file_get_contents($ssl_certificate->getChain())
            ));
        } catch(Exception $e) {
            $form = $this->crawler->filter('form')->form(array(
                "v_ssl" => "on",
                "v_ssl_home" => "same",
                "v_ssl_crt" => file_get_contents($ssl_certificate->getCertificate()),
                "v_ssl_key" => file_get_contents($ssl_certificate->getPrivate()),
                "v_ssl_ca" => file_get_contents($ssl_certificate->getChain())
            ));
        }

        $this->crawler = $this->client->submit($form);

        return true;
    }
}