<?php

class LoyaltyCard_View_Mobile_View extends Core_View_Mobile_Default
{

    protected $_customer;
    protected $_loyaltycards;

    public function __construct($config = array()) {
        parent::__construct($config);
        $this->_customer = $this->getSession()->getCustomer();
    }

    public function getLoyaltyCards() {

        if(is_null($this->_loyaltycards)) {
            $fcc = new LoyaltyCard_Model_Customer();
            $customer_id = $this->getCustomer()->getId() | 0;
            $this->_loyaltycards = $fcc->findAllByOptionValue($this->getCurrentOption()->getId(), $customer_id);
        }

        return $this->_loyaltycards;
    }

    public function getCustomer() {
        return $this->_customer;
    }

}