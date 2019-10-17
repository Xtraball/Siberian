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
        return $this->request($method, $endpoint, $parameters, $files, $server, $content, $changeHistory);
    }
}