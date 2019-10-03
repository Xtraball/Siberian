<?php

namespace Crashlytics\Http;

/**
 * Class Request
 * @package Crashlytics\Http
 */
class Request
{
    /**
     * @var string
     */
    CONST API_ENDPOINT = "https://api.crashlytics.com";

    /**
     * @var array
     */
    CONST WHITELIST = [
        "*.crashlytics.com",
        "*.fabric.io"
    ];

    /**
     * @var Client
     */
    private $client;

    /**
     * @var
     */
    private $endpoint;

    /**
     * Request constructor.
     * @param $endpoint
     * @param $crashReport
     */
    public function __construct($endpoint, $crashReport)
    {
        $this->endpoint = $endpoint;
        $this->client = new Client();

        if (sizeof(self::WHITELIST) > 0) {
            $this->client->_request(
                "POST",
                self::API_ENDPOINT,
                [],
                [],
                ['HTTP_CONTENT_TYPE' => 'application/x-www-form-urlencoded'],
                $crashReport);
        }
    }
}