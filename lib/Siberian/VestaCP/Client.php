<?php
/**
 * Xtraball SAS <dev@xtraball.com>
 *
 * Version 1.0
 */

use Goutte\Client as VestaClient;

class Siberian_VestaCP_Client extends VestaClient {

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
    public function _request($method, $endpoint, array $parameters = array(), array $files = array(), array $server = array(), $content = null, $changeHistory = true) {
        return $this->request($method, $endpoint, $parameters, $files, $server, $content, $changeHistory);
    }
}