<?php

namespace Siberian;

/**
 * Class Exception
 * @package Siberian
 * @version 4.14.0
 */
class Exception extends \Exception
{
    const CODE_FW = 1024;

    /**
     * Exception constructor.
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct($message = '', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        log_exception($this);
    }

}