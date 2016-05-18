<?php

class Mcommerce_Model_Delivery_Method extends Core_Model_Default {

    protected $_instance;

    protected $_cart;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Mcommerce_Model_Db_Table_Delivery_Method';
        return $this;
    }

    public function findByStore($id = null) {
        if(!$id) $id = -1;
        return $this->getTable()->findByStore($id);
    }

    public function saveStoreDatas($store_id, $method_datas) {
        $this->getTable()->saveStoreDatas($store_id, $method_datas);
        return $this;
    }

    public function getName() {
        return $this->_($this->getData('name'));
    }

    public function getInstance() {

        if(!$this->_instance) {
            $class = get_class($this).'_'.ucfirst(str_replace('_', '', $this->getCode()));
            try {
                if(!@class_exists($class)) {
                    $class = get_class($this).'_Default';
                }
            } catch (Exception $ex) {
                $class = get_class($this).'_Default';
            }

            $this->_instance = new $class();
            $this->_instance->setMethod($this);
        }

        return $this->_instance;
    }

    public function isAvailable() {
        return $this->getInstance()->isAvailable();
    }

    public function isFree() {
        return $this->getInstance()->isFree();
    }

    public function customerAddressIsRequired() {
        return $this->getInstance()->customerAddressIsRequired();
    }

    public function setCart($cart) {
        $this->_cart = $cart;
//        $this->getCart()->getSubtotalInclTax();
        if($cart->getSubtotalInclTax() > $this->getMinAmountForFreeDelivery()) {
            $this->setPrice(0);
        }
        return $this;
    }

    public function getCart() {
        return $this->_cart ? $this->_cart : new Mcommerce_Model_Cart();
    }

}
