<?php

namespace Vnn\WpApiClient\Auth;

use Psr\Http\Message\RequestInterface;

/**
 * Interface AuthInterface
 * @package Vnn\WpApiClient\Auth
 */
interface AuthInterface
{
    /**
     * @param RequestInterface $request
     * @return mixed
     */
    public function addCredentials(RequestInterface $request);
}
