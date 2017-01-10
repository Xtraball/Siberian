<?php

/**
 * Class Siberian_Exception
 *
 * @version 4.8.7
 *
 */

class Siberian_Exception extends Exception {

    public function __construct($message, $code, Exception $previous) {
        log_debug(sprintf("[Siberian_Exception] %s", $message));

        parent::__construct($message, $code, $previous);
    }

}
