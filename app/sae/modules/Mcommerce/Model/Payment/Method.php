<?php

class Mcommerce_Model_Payment_Method extends Core_Model_Default {

    protected $_instance;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Mcommerce_Model_Db_Table_Payment_Method';
        return $this;
    }

    public function findByStore($id = null) {
        if(!$id) $id = -1;
        return $this->getTable()->findByStore($id);
    }

    public function saveStoreDatas($store_id, $method_datas) {

        $this->getTable()->saveStoreDatas($store_id, $method_datas);
        foreach($method_datas as $method_data) {
            $instance = $this->find($method_data['method_id'])->setStoreId($store_id)->getInstance();
            $method_data['store_id'] = $store_id;
            $instance->setData($method_data)->save();
            $this->_instance = null;
        }
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

    public function getUrl($value_id = null) {
        return $this->getInstance()->getUrl($value_id);
    }

    public function getFormUrl($value_id = null) {
        return $this->getInstance()->getFormUrl($value_id);
    }

    public function isOnline() {
        return $this->getInstance()->isOnline();
    }

    public function isCurrencySupported() {
        return $this->getInstance()->isCurrencySupported();
    }

    public function pay() {
        return $this->getInstance()->pay();
    }

    public function saveCardAndPay($data) {
        return $this->getInstance()->saveCardAndPay($data);
    }

    public function payByCustomerToken($charge_data) {
        return $this->getInstance()->payByCustomerToken($charge_data);
    }

}
