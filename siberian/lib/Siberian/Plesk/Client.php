<?php
/**
 * Xtraball SAS <dev@xtraball.com>
 *
 * Version 1.0
 */

use Goutte\Client as PleskClient;

/**
 * Class Siberian_Plesk_Client
 */
class Siberian_Plesk_Client extends PleskClient
{

    /**
     * @param $method
     * @param $endpoint
     * @param array $parameters
     * @param array $files
     * @param array $server
     * @param null $content
     * @param bool $changeHistory
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    public function _request($method, $endpoint, array $parameters = [], array $files = [],
                             array $server = [], $content = null, $changeHistory = true)
    {
        return $this->request($method, $endpoint, $parameters, $files, $server, $content, $changeHistory);
    }
}