<?php

namespace Siberian\Firebase;

/**
 * Class Api
 * @package Siberian\Firebase
 */
class Utils
{
    /**
     * @param $html
     * @return bool
     */
    public static function extractApiKey ($html) {
        preg_match('#\\\x22api-key\\\x22:\\\x22(.*?)\\\x22#mi', $html, $matches);

        if (isset($matches[1])) {
            return $matches[1];
        }

        return false;
    }

    /**
     * @param $time
     * @param $sapisid
     * @param $origin
     * @return string
     */
    public static function generateSapisidHash($time, $sapisid, $origin)
    {
        $toHash = $time . ' ' . $sapisid . ' ' . $origin;
        $hash = sha1($toHash);
        $sapisidHash = $time . '_' . $hash;

        return $sapisidHash;
    }

    /**
     * @param $cookie
     * @param string $cookieName
     * @param string $path
     * @param string $domain
     * @param string $firebaseConsole
     * @return mixed
     */
    public static function createHeaders ($cookie,
                                          $cookieName = 'SAPISID',
                                          $path = '/',
                                          $domain = '.google.com',
                                          $firebaseConsole = 'https://console.firebase.google.com')
    {
        $sapsid = $cookie->get($cookieName, $path, $domain)->getValue();
        $time = time();
        $sapisidHash = self::generateSapisidHash($time, $sapsid, $firebaseConsole);

        return $sapisidHash;
    }

    /**
     * @param $cookie
     * @param array $mergedDomains
     * @return string
     */
    public static function cookieHeaders ($cookie,
                                          $mergedDomains = ['.google.com', 'console.firebase.google.com'])
    {
        $cookies = $cookie->all();
        $cookieHeaders = [];

        // We merge domain cookies in the defined order from the Array $mergedDomains
        foreach ($mergedDomains as $mergedDomain) {
            foreach ($cookies as $cookie) {
                if ($cookie->getDomain() === $mergedDomain) {
                    $cookieHeaders[$cookie->getName()] = $cookie->getValue();
                }
            }
        }

        $heads = [];
        foreach ($cookieHeaders as $name => $value) {
            $heads[] = $name . '=' . $value;
        }

        return join(';', $heads);
    }

    /**
     * @param $adminIdCredentials
     * @param $application
     * @throws \Siberian_Exception
     */
    public static function configureForFirebase ($adminIdCredentials, $application)
    {
        $credentials = (new \Push_Model_Firebase())
            ->find($adminIdCredentials, 'admin_id');

        if (!$credentials->getId()) {
            throw new \Siberian_Exception('#908-02: ' .
                __('You must set your Firebase credentials in order to generate Android applications'));
        }

        $device = $application->getDevice(2);
        $appName = $application->getName();
        $packageName = $application->getPackageName();
        $projectNumber = $credentials->getProjectNumber();

        $creds = $credentials->getCredentials();

        $firebase = new \Siberian\Firebase\Api();
        $firebase->login($creds['email'], $creds['password']);

        // Download the configuration
        $result = $firebase->packageNameExists($packageName);

        $configuration = [];

        if ($result !== false) {
            $googleServiceConfig = $firebase->downloadConfig(
                $result['projectNumber'],
                $result['clientId']);
            $googleServiceShort = self::extractSingleConfig($googleServiceConfig, $packageName);

            $device
                ->setGoogleServices($googleServiceShort)
                ->save();

            $configuration['projectNumber'] = $result['projectNumber'];
            $configuration['clientId'] = $result['clientId'];
            $configuration['googleService'] = $googleServiceShort;
        } else {
            // Create the application!
            $addClientResponse = $firebase->addClient($projectNumber, $appName, $packageName);
            $clientResponse = \Siberian_Json::decode($addClientResponse);

            $googleServiceConfig = $firebase->downloadConfig(
                $clientResponse['client']['projectNumber'],
                $clientResponse['client']['clientId']);
            $googleServiceShort = self::extractSingleConfig($googleServiceConfig, $packageName);

            $device
                ->setGoogleServices($googleServiceShort)
                ->save();

            $configuration['projectNumber'] = $clientResponse['client']['projectNumber'];
            $configuration['clientId'] = $clientResponse['client']['clientId'];
            $configuration['googleService'] = $googleServiceShort;
        }

        return $configuration;
    }

    /**
     * As a single project can handle hundreds of Android applications,
     * we simply extract the good one resulting in a smaller json file!
     *
     * @param $googleServiceConfig
     * @param $packageName
     * @return mixed
     */
    public static function extractSingleConfig ($googleServiceConfig, $packageName)
    {
        // Backup clients
        $googleService = \Siberian_Json::decode($googleServiceConfig);
        $clients = $googleService['client'];

        // Clear list
        $googleService['client'] = [];
        foreach ($clients as $client) {
            if (array_key_exists('client_info', $client) &&
                array_key_exists('android_client_info', $client['client_info']) &&
                array_key_exists('package_name', $client['client_info']['android_client_info']) &&
                $client['client_info']['android_client_info']['package_name'] === $packageName) {

                $googleService['client'][] = $client;
                break;
            }
        }

        return $googleService;
    }
}