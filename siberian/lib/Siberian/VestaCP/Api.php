<?php

namespace Siberian\VestaCP;

use Siberian\Exception;

/**
 * Class Api
 * @package Siberian\VestaCP
 */
class Api
{
    /**
     * @var Client
     */
    public $client;

    /**
     * @var \Symfony\Component\DomCrawler\Crawler
     */
    public $crawler;

    /**
     * Api constructor.
     * @param $host
     * @param $username
     * @param $password
     * @param $webspace
     */
    public function __construct($host, $username, $password, $webspace)
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->webspace = $webspace;
        $this->client = new Client();
    }

    /**
     *
     */
    public function login()
    {
        /** For no reason, sometimes one method works, sometimes the other one */
        try {
            $this->crawler = $this->client->_request("GET", $this->host . "/login/");

            $form = $this->crawler->selectButton("Log in")->form([
                "user" => $this->username,
                "password" => $this->password
            ]);

            $this->client->submit($form);
        } catch (Exception $e) {
            $this->crawler = $this->client->_request("POST", $this->host . "/login/", [
                "user" => $this->username,
                "password" => $this->password
            ]);
        }

        $this->crawler = $this->client->_request("GET", $this->host);
    }

    /**
     * @param $ssl_certificate
     * @return bool
     */
    public function updateDomain($ssl_certificate)
    {

        $webspace = $ssl_certificate->getHostname();
        if (!empty($this->webspace)) {
            $webspace = $this->webspace;
        }

        $this->crawler = $this->client->_request("GET", $this->host . "/edit/web/?domain=" . $webspace);

        try {
            $form = $this->crawler->selectButton("Save")->form([
                "v_ssl" => "on",
                "v_ssl_home" => "same",
                "v_ssl_crt" => file_get_contents($ssl_certificate->getCertificate()),
                "v_ssl_key" => file_get_contents($ssl_certificate->getPrivate()),
                "v_ssl_ca" => file_get_contents($ssl_certificate->getChain())
            ]);
        } catch (Exception $e) {
            $form = $this->crawler->filter('form')->form([
                "v_ssl" => "on",
                "v_ssl_home" => "same",
                "v_ssl_crt" => file_get_contents($ssl_certificate->getCertificate()),
                "v_ssl_key" => file_get_contents($ssl_certificate->getPrivate()),
                "v_ssl_ca" => file_get_contents($ssl_certificate->getChain())
            ]);
        }

        $this->crawler = $this->client->submit($form);

        return true;
    }

    /**
     * @param $ssl_certificate
     * @return bool
     * @throws Exception
     */
    public function updateDomainVesta($ssl_certificate)
    {
        $webspace = $ssl_certificate->getHostname();
        if (!empty($this->webspace)) {
            $webspace = $this->webspace;
        }

        $base = path("/var/apps/certificates/");
        $folder = $base . '/' . $ssl_certificate->getHostname();

        // Coy the files
        copy($folder . '/cert.pem', $folder . '/' . $webspace . '.crt');
        copy($folder . '/private.pem', $folder . '/' . $webspace . '.key');
        copy($folder . '/fullchain.pem', $folder . '/' . $webspace . '.ca');

        // Prepare POST query
        $postvars = [
            'user' => $this->username,
            'password' => $this->password,
            'returncode' => 'yes',
            'cmd' => 'v-add-web-domain-ssl',
            'arg1' => $this->username,
            'arg2' => $webspace,
            'arg3' => $folder,
            'arg4' => 'RESTART'
        ];

        $postdata = http_build_query($postvars);
        // Send POST query via cURL
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->host . '/api/');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
        $answer = curl_exec($curl);

        // Check result
        if ($answer == 0) {
            return true;
        } else {
            if ($answer == 4) {
                $postvars = [
                    'user' => $this->username,
                    'password' => $this->password,
                    'returncode' => 'yes',
                    'cmd' => 'v-update-web-domain-ssl',
                    'arg1' => $this->username,
                    'arg2' => $webspace,
                    'arg3' => $folder,
                    'arg4' => 'RESTART'
                ];
                $postdata = http_build_query($postvars);
                // Send POST query via cURL
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $this->host . '/api/');
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
                $answer = curl_exec($curl);
                if ($answer == 0) {
                    return true;
                }
            }
        }

        throw new Exception("Error SSL : Vesta API Query returned error code: " . $answer);
    }

}