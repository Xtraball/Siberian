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
        return $this->request($method, $this->getTlsEndpoint($endpoint), $parameters, $files, $server, $this->buildContent($content), $changeHistory);
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
     * @param $content
     * @return string
     */
    private function buildContent($content)
    {
        $crashReport = base64_encode($content);
        return "crash-report={$crashReport}";
    }
}