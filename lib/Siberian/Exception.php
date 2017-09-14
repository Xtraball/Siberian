<?php

/**
 * Class Siberian_Exception
 *
 * @version 4.8.7
 *
 */

class Siberian_Exception extends Exception {

    public function __construct($message = '', $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);

        log_exception($this);
    }

}
