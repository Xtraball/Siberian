<?php

use PHP_GCM\Sender as Sender;

class Siberian_Service_Push_Gcm extends Sender {

    public function __construct($key) {
        parent::__construct($key);
    }
}