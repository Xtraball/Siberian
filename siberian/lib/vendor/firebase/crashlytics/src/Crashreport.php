<?php

namespace Crashlytics;

/**
 * Class Crashreport
 * @package Crashlytics
 */
class Crashreport
{
    /**
     * @var string
     */
    private $credentials;

    /**
     * Crashreport constructor.
     * @param $credentials
     */
    public function __construct($credentials, $crashReport)
    {
        $this->credentials = $credentials;
        $request = new Http\Request("crashreport", $crashReport);
    }
}