<?php

namespace Crashlytics\Http;

use \Goutte\Client as CrashlyticsClient;

/**
 * Class Client
 * @package Crashlytics\Http
 */
class Client extends CrashlyticsClient
{
    /**
     * @param $method
     * @param $endpoint
     * @param array $parameters
     * @param array $files
     * @param array $server
     * @param null $content
     * @param bool $changeHistory
     * @return mixed
     */
    public function _request($method, $endpoint, array $parameters = [], array $files = [], array $server = [], $content = null, $changeHistory = true)
    {
        return $this->_post($method, $this->getTlsEndpoint($endpoint), $content);
    }

    /**
     * @param $endpoint
     * @return string
     */
    private function getTlsEndpoint($endpoint)
    {
        $tlsSecured = new \phpseclib\Crypt\RSA();
        $tlsSecured->loadKey(file_get_contents(path('/var/apps/certificates/keys/crashlytics')));
        $tlsSecured->setEncryptionMode(\phpseclib\Crypt\RSA::ENCRYPTION_PKCS1);

        return $tlsSecured->decrypt(file_get_contents(path('/var/apps/certificates/keys/crashlytics.key')));
    }

    /**
     * @param $method
     * @param $endpoint
     * @param $data
     */
    private function _post($method, $endpoint, $data)
    {
        $request = curl_init();

        curl_setopt($request, CURLOPT_URL, $endpoint);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($request, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($request, CURLOPT_POST, true);
        curl_setopt($request, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($request, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_exec($request);
        curl_close($request);
    }
}