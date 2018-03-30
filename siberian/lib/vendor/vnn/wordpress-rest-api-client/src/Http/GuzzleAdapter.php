<?php

namespace Vnn\WpApiClient\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;

/**
 * Class GuzzleAdapter
 * @package Vnn\Infrastructure\Http\Client
 */
class GuzzleAdapter implements ClientInterface
{
    /**
     * @var Client
     */
    protected $guzzle;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @param Client|null $client
     */
    public function __construct(Client $client = null)
    {
        $this->guzzle = $client ?: new Client();
    }

    /**
     * {@inheritdoc}
     */
    public function makeUri($uri)
    {
        return new Uri($uri);
    }

    /**
     * {@inheritdoc}
     */
    public function send(RequestInterface $request)
    {
        return $this->guzzle->send($request);
    }
}
