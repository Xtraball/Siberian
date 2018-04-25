<?php

namespace Vnn\WpApiClient\Endpoint;

use GuzzleHttp\Psr7\Request;
use RuntimeException;
use Vnn\WpApiClient\WpClient;

/**
 * Class AbstractWpEndpoint
 * @package Vnn\WpApiClient\Endpoint
 */
abstract class AbstractWpEndpoint
{
    /**
     * @var WpClient
     */
    private $client;

    /**
     * Users constructor.
     * @param WpClient $client
     */
    public function __construct(WpClient $client)
    {
        $this->client = $client;
    }

    abstract protected function getEndpoint();

    /**
     * @param int $id
     * @param array $params - parameters that can be passed to GET
     *        e.g. for tags: https://developer.wordpress.org/rest-api/reference/tags/#arguments
     * @return array
     */
    public function get($id = null, array $params = null)
    {
        $uri = $this->getEndpoint();
        $uri .= (is_null($id)?'': '/' . $id);
        $uri .= (is_null($params)?'': '?' . http_build_query($params));

        $request = new Request('GET', $uri);
        $response = $this->client->send($request);

        if ($response->hasHeader('Content-Type')
            && substr($response->getHeader('Content-Type')[0], 0, 16) === 'application/json') {
            return json_decode($response->getBody()->getContents(), true);
        }

        throw new RuntimeException('Unexpected response');
    }

    /**
     * @param array $data
     * @return array
     */
    public function save(array $data)
    {
        $url = $this->getEndpoint();

        if (isset($data['id'])) {
            $url .= '/' . $data['id'];
            unset($data['id']);
        }

        $request = new Request('POST', $url, ['Content-Type' => 'application/json'], json_encode($data));
        $response = $this->client->send($request);

        if ($response->hasHeader('Content-Type')
            && substr($response->getHeader('Content-Type')[0], 0, 16) === 'application/json') {
            return json_decode($response->getBody()->getContents(), true);
        }

        throw new RuntimeException('Unexpected response');
    }
}
