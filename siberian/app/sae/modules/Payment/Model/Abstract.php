<?php

/**
 * Class Payment_Model_Abstract
 */
abstract class Payment_Model_Abstract extends Core_Model_Default {

    /**
     * @var
     */
    protected $_code;

    /**
     * @return array
     */
    public function getPaymentData() {
        return array();
    }

    /**
     * @return bool
     */
    public function success() {
        return false;
    }

    /**
     * @return bool
     */
    public function manageRecurring() {
        return false;
    }

    /**
     * @return mixed
     */
    public function getCode() {
        return $this->_code;
    }

}