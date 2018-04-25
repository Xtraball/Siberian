<?php

namespace Vnn\WpApiClient\Auth;

use Psr\Http\Message\RequestInterface;

/**
 * Class WpBasicAuth
 * @package Vnn\WpApiClient\Auth
 */
class WpBasicAuth implements AuthInterface
{
    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * WpBasicAuth constructor.
     * @param string $username
     * @param string $password
     */
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * {@inheritdoc}
     */
    public function addCredentials(RequestInterface $request)
    {
        return $request->withHeader(
            'Authorization',
            'Basic ' . base64_encode($this->username . ':' . $this->password)
        );
    }
}
