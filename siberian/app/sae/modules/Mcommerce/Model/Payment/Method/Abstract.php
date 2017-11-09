<?php

abstract class Mcommerce_Model_Payment_Method_Abstract extends Core_Model_Default {
    private $_supported_currency_codes = array();

    public function pay($id = null) {
        return false;
    }

    public function isOnline() {
        return false;
    }

    public function isCurrencySupported() {
        return true;
    }

}
