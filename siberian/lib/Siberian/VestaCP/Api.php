<?php

/**
 * Class Siberian_VestaCP_Api
 */
class Siberian_VestaCP_Api
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
     * Apps constructor.
     *
     * Login into dashboard
     *
     * @param $host
     * @param $password
     * @param $password
     * @param $webspace
     */
    public function __construct($host, $username, $password, $webspace)
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->webspace = $webspace;
        $this->client = new Siberian_VestaCP_Client();

        //$this->login();
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
     * @throws \Siberian\Exception
     */
    public function updateDomainVesta($ssl_certificate)
    {
        $mainDomain = __get('main_domain');
        $base = Core_Model_Directory::getBasePathTo("/var/apps/certificates/");
        $folder = $base . '/' . $mainDomain;

        // Coy the files
        copy($folder . '/cert.pem', $folder . '/' . $mainDomain . '.crt');
        copy($folder . '/private.pem', $folder . '/' . $mainDomain . '.key');
        copy($folder . '/fullchain.pem', $folder . '/' . $mainDomain . '.ca');

        // Prepare POST query
        $postvars = [
            'user' => $this->username,
            'password' => $this->password,
            'returncode' => 'yes',
            'cmd' => 'v-add-web-domain-ssl',
            'arg1' => $this->username,
            'arg2' => $mainDomain,
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
                    'arg2' => $mainDomain,
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
            }
            throw new \Siberian\Exception("Error SSL : Vesta API Query returned error code: " . $answer);
        }
    }

}