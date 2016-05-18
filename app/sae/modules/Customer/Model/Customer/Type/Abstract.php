<?php

abstract class Customer_Model_Customer_Type_Abstract extends Core_Model_Default
{

    protected $_id;

    protected $_customer;

    public function __construct($params = array()) {
        $this->_id = isset($params['social_id']) ? $params['social_id'] : null;
        return $this;
    }

    public function setId($id) {
        $this->_id = $id;
        return $this;
    }

    public function getId() {
        return $this->_id;
    }

    public function setCustomer($customer) {
        $this->_customer = $customer;
        return $this;
    }

    public function getCustomer() {
        return $this->_customer;
    }

    public function isValid() {
        return false;
    }

    public function postMessage($pos) {
        return $this;
    }

}