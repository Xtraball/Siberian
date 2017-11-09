<?php

abstract class Mcommerce_Model_Delivery_Method_Abstract extends Core_Model_Default {

    public function isAvailable() {
        return true;
    }

    public function isFree() {
        return true;
    }

    public function customerAddressIsRequired() {
        return false;
    }

}
