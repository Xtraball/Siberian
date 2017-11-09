<?php

class Customer_View_Email_Default extends Core_View_Email_Default
{
    
    protected $_customer;
    protected $_password;
    
    public function setCustomer($customer) {
        $this->_customer = $customer;
        return $this;
    }
    
    public function getCustomer() {
        return $this->_customer;
    }
    
    public function setPassword($password) {
        $this->_password = $password;
        return $this;
    }
    
    public function getPassword() {
        return $this->_password;
    }
    
}
