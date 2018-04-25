<?php

namespace Vnn\WpApiClient\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * Interface ClientInterface
 * @package Vnn\WpApiClient\Http
 */
interface ClientInterface
{
    /**
     * @param string $uri
     * @return UriInterface
     */
    public function makeUri($uri);

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function send(RequestInterface $request);
}
