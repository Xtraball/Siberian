<?php

class Customer_Model_Session extends Core_Model_Session_Instance_Abstract
{

    public function __construct($params) {
        $customer = new Customer_Model_Customer();
        $customer->find($params['id']);
        $this->setObject($customer);
    }

    public function isLoggedIn() {
        return $this->getObject() && $this->getObject()->getId();
    }

    public function getAccountUri() {
        return 'customer/account/view';
    }

    public function getLogoutUri() {
        return 'customer/account/logout';
    }
}