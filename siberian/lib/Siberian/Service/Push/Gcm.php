<?php

use PHP_GCM\Sender as Sender;

class Siberian_Service_Push_Gcm extends Sender {

    /**
     * Siberian_Service_Push_Gcm constructor.
     * @param string $key
     */
    public function __construct($key) {
        parent::__construct($key);
    }
}