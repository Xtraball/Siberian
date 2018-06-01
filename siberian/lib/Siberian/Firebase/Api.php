<?php

namespace Siberian\Firebase;

/**
 * Class Api
 * @package Siberian\Firebase
 */
class Api
{
    /**
     * @var array
     */
    public $clients = [];

    /**
     * @var string
     */
    protected $baseUrl = 'https://console.firebase.google.com';

    /**
     * @var string
     */
    protected $sdkUrl = 'https://mobilesdk-pa.clients6.google.com/';

    /**
     * @var string
     */
    protected $loginUrl = 'https://accounts.google.com/ServiceLogin';

    /**
     * @var \Goutte\Client
     */
    protected $client;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var array
     */
    private $headers = [];

    /**
     * @param $email
     * @param $password
     * @throws \Siberian_Exception
     */
    public function login ($email, $password)
    {
        $this->client = new \Goutte\Client();

        $firebaseLoginUri = sprintf("%s?passive=1209600&osid=1&continue=%s/&followup=%s",
            $this->loginUrl,
            $this->baseUrl,
            $this->baseUrl);

        try {
            $this->client->followRedirects(true);
            $this->client->setHeader('user-agent', 'BrowserKit/3.1 (Siberian; 4.14)');
            $crawler = $this->client->request('GET', $firebaseLoginUri, [
                'allow_redirects' => true,
            ]);

            $form = $crawler->selectButton('Next')->form();
            $crawler = $this->client->submit($form, [
                'Email' => $email,
            ]);

            $form = $crawler->selectButton('Sign in')->form();
            $crawler = $this->client->submit($form, [
                'Passwd' => $password,
            ]);
        } catch (\Exception $e) {
            throw new \Siberian_Exception('#307-00: ' .
                __('Unable to login, please check your Firebase credentials, note that if you have two-factor auth enabled, this will not work.'));
        }

        $html = $crawler->html();

        $this->apiKey = Utils::extractApiKey($html);

        if ($this->apiKey === false) {
            throw new \Siberian_Exception('#307-01: ' .
                __('Unable to login, please check your Firebase credentials, note that if you have two-factor auth enabled, this will not work.'));
        }

        // Building cookies & headers
        $cookieHeaders = Utils::cookieHeaders($this->client->getCookieJar());
        $auth = Utils::createHeaders($this->client->getCookieJar());

        $this->headers = [
            'Authorization: SAPISIDHASH ' . $auth,
            'Cookie: ' . $cookieHeaders,
            'X-Origin: https://console.firebase.google.com',
            'User-Agent: BrowserKit/3.1 (Siberian; 4.14)',
        ];

        // List available clients
        try {
            $this->clients = \Siberian_Json::decode($this->listClients());
        } catch (\Exception $e) {
            throw new \Siberian_Exception('#307-02: ' .
                __('Unable fetch your projects.'));
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getClients ()
    {
        return $this->clients;
    }

    /**
     * @return array
     * @throws \Siberian_Exception
     */
    public function getProjects ()
    {
        if (!isset($this->clients['project'])) {
            throw new \Siberian_Exception('#307-03: ' .
                __('Please create at least one Firebase project.'));
        }

        $projects = [];
        foreach ($this->clients['project'] as $project) {
            $projects[] = [
                'projectId' => $project['projectId'],
                'projectNumber' => $project['projectNumber'],
                'displayName' => $project['displayName'],
            ];
        }

        return $projects;
    }

    /**
     * @param $packageName
     * @return array|bool
     */
    public function packageNameExists ($packageName)
    {
        $clients = $this->getClients();
        $projects = $clients['project'];
        foreach ($projects as $project) {
            foreach ($project['clientSummary'] as $client) {
                // Filter only android projects!
                if ((strpos($client['clientId'], 'android:') === 0) &&
                    ($packageName === $client['androidClientSummary']['packageName'])) {
                    return [
                        'projectNumber' => $project['projectNumber'],
                        'clientId' => $client['clientId'],
                    ];
                }
            }
        }
        return false;
    }

    /**
     * @return array
     * @throws \Siberian_Exception
     */
    public function getApplications ($projectNumber)
    {
        if (!isset($this->clients['project'])) {
            throw new \Siberian_Exception('#307-04: ' .
                __('Please create at least one Firebase project.'));
        }

        $project = null;
        foreach ($this->clients['project'] as $project) {
            if ($project['projectNumber'] === $projectNumber) {
                break;
            }
        }

        if (!isset($project['clientSummary'])) {
            throw new \Siberian_Exception('#307-05: ' .
                __('You have no client applications in your Firebase project.'));
        }

        $clients = [];
        foreach ($project['clientSummary'] as $client) {
            // Filter only android projects!
            if (strpos($client['clientId'], 'android:') === 0) {
                $clients[] = [
                    'clientId' => $client['clientId'],
                    'mobilesdkAppId' => $client['mobilesdkAppId'],
                    'packageName' => $client['androidClientSummary']['packageName'],
                ];
            }
        }

        return $clients;
    }

    /**
     * @param $projectNumber
     * @return mixed
     */
    public function getProjectSettings ($projectNumber)
    {
        $settingsUri = sprintf("%s/v1/projects/%s/settings/cloudmessaging?key=%s",
            $this->sdkUrl,
            $projectNumber,
            $this->apiKey);

        $request = curl_init();

        # Setting options
        curl_setopt($request, CURLOPT_URL, $settingsUri);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($request, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($request, CURLOPT_TIMEOUT, 30);
        curl_setopt($request, CURLOPT_HTTPHEADER, $this->headers);

        # Call
        $result = curl_exec($request);

        curl_close($request);

        return $result;
    }

    /**
     * @return mixed
     */
    public function listClients ()
    {
        // Fetch clients
        $endpoint = sprintf("%s/v1/projects?key=%s",
                $this->sdkUrl,
                $this->apiKey);

        $request = curl_init();

        # Setting options
        curl_setopt($request, CURLOPT_URL, $endpoint);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($request, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($request, CURLOPT_TIMEOUT, 30);
        curl_setopt($request, CURLOPT_HTTPHEADER, $this->headers);

        # Call
        $result = curl_exec($request);

        curl_close($request);

        return $result;
    }

    /**
     * @param $projectNumber
     * @param $appName
     * @param $packageName
     * @return mixed
     */
    public function addClient ($projectNumber, $appName, $packageName)
    {
        // Create a new Application
        $params = [
            "requestHeader" => [
                "clientVersion" => "FIREBASE"
            ],
            "displayName" => $appName,
            "androidData" => [
                "packageName" => $packageName
            ]
        ];

        $jsonBody = json_encode($params);

        // Create a client
        $endpoint = sprintf("%s/v1/projects/%s/clients?key=%s",
            $this->sdkUrl,
            $projectNumber,
            $this->apiKey);

        $headers = array_merge($this->headers, [
            'Content-Type: application/json',
            'Content-Length: ' . mb_strlen($jsonBody),
        ]);

        $request = curl_init();

        # Setting options
        curl_setopt($request, CURLOPT_URL, $endpoint);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($request, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($request, CURLOPT_TIMEOUT, 30);
        curl_setopt($request, CURLOPT_POST, true);
        curl_setopt($request, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($request, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($request, CURLOPT_POSTFIELDS, $jsonBody);

        # Call
        $result = curl_exec($request);

        curl_close($request);

        return $result;
    }

    /**
     * @param $endpoint
     * @param $headers
     * @return mixed
     */
    public function deleteClient ($projectNumber, $clientId)
    {
        // Fetch clients
        $endpoint = sprintf("%s/v1/projects/%s/clients/%s?key=%s",
            $this->sdkUrl,
            $projectNumber,
            $clientId,
            $this->apiKey);

        $request = curl_init();

        # Setting options
        curl_setopt($request, CURLOPT_URL, $endpoint);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($request, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($request, CURLOPT_TIMEOUT, 30);
        curl_setopt($request, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($request, CURLOPT_HTTPHEADER, $this->headers);

        # Call
        $result = curl_exec($request);

        curl_close($request);

        return $result;
    }

    /**
     * @param $projectNumber
     * @param $clientId
     * @return mixed
     */
    public function downloadConfig ($projectNumber, $clientId)
    {
        $request = "[\"getArtifactRequest\",null,\"$clientId\",\"1\",\"$projectNumber\"]";

        $endpoint = sprintf("%s/m/mobilesdk/projects/%s/clients/%s/artifacts/2?param=%s",
            $this->baseUrl,
            $projectNumber,
            urlencode($clientId),
            urlencode($request));

        $request = curl_init();

        # Setting options
        curl_setopt($request, CURLOPT_URL, $endpoint);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($request, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($request, CURLOPT_TIMEOUT, 30);
        curl_setopt($request, CURLOPT_HTTPHEADER, $this->headers);

        # Call
        $result = curl_exec($request);

        curl_close($request);

        return $result;
    }
}