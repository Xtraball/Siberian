<?php

/**
 * Class Siberian_Plesk_Crawler
 *
 * PHP 5.6+ only
 */
class Siberian_Plesk_Crawler {

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
     * @param $username
     * @param $password
     */
    public function __construct($host, $username, $password) {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->client = new Siberian_Plesk_Client();

        $this->login();
    }

    /**
     * @throws Exception
     */
    public function login() {
        /** For no reason, sometimes one method works, sometimes the other one */
        try {
            $this->crawler = $this->client->_request("GET", $this->host."/login_up.php3");

            $forgery_tokens = $this->crawler->filterXPath('//head/meta[@name="forgery_protection_token"]');

            $forgery_token = "";
            foreach($forgery_tokens as $t) {
                $forgery_token = $t->getAttribute("content");
            }

            $this->crawler = $this->client->_request("POST", $this->host."/login_up.php3", array(
                "login_name" => $this->username,
                "passwd" => $this->password,
                "success_redirect_url" => $this->host."/admin/domain/list?context=domains",
                "forgery_protection_token" => $forgery_token,
            ));

        } catch(Exception $e) {
            throw new Exception("[Siberian_Plesk_Crawler]: Unable to log in Plesk, with message %s.", $e->getMessage());
        }
    }

    /**
     * Crawling Plesk site to change SSL certificate
     *
     * Plesk 12, 12.5, 17 Tested
     *
     * @param $hostname
     * @param $id
     * @return bool
     * @throws Exception
     */
    public function updateDomain($hostname, $id) {
        $this->crawler = $this->client->_request("GET", $this->host."/smb/web/settings/id/".$id);

        try {
            $cert_name = sprintf("%s-%s", "siberian_letsencrypt", $hostname);
            $options = $this->crawler->filterXPath('//select[@id="sslSettings-certificateId"]/option');
            $certificate_id = null;
            foreach($options as $option) {
                if(strpos($option->nodeValue, $cert_name) !== false) {
                    $certificate_id = $option->getAttribute("value");
                    break;
                }
            }

            $form = $this->crawler->filterXPath('//form[contains(@id, "web-settings")]')->form(array(
                "sslSettings[certificateId]" => $certificate_id,
            ));

            $this->crawler = $this->client->submit($form);
        } catch(Exception $e) {
            throw new Exception(__("[Siberian_Plesk_Crawler]: Unable to save certificate, with message %s.", $e->getMessage()));
        }

        return true;
    }
}