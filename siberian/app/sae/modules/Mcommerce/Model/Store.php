<?php

class Mcommerce_Model_Store extends Core_Model_Default {

    protected $_taxes;
    protected $_delivery_methods;
    protected $_delivery_method_ids;
    protected $_payment_methods;
    protected $_payment_method_ids;
    protected $_printer;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Mcommerce_Model_Db_Table_Store';
        return $this;
    }

    public function save() {

        parent::save();

        $tax = new Mcommerce_Model_Tax();

        if($this->getNewTaxRate()) {
            $tax_rate = str_replace(',', '.', $this->getNewTaxRate());
            $tax_rate = floatval(preg_replace('/[^0-9.]*/', '', $tax_rate));
            if($tax_rate < 1) $tax_rate *= 100;
            $tax_datas = array(
                'mcommerce_id' => $this->getMcommerceId(),
                'name' => $tax_rate.'%',
                'rate' => $tax_rate
//                'store_taxes' => array(
//                    $this->getId() => $tax_rate
//                )
            );

            $tax->setData($tax_datas)->save();
            $this->_taxes = null;
        }

        if($this->getNewDeliveryMethods()) {
            $delivery_method_datas = $this->getNewDeliveryMethods();
            foreach($delivery_method_datas as $key => $delivery_method_data) {
                if(!empty($delivery_method_data['price']) AND empty($delivery_method_data['tax_id']) AND $tax->getId()) {
                    $delivery_method_data['tax_id'] = $tax->getId();
                    $delivery_method_datas[$key] = $delivery_method_data;
                }
            }

            $delivery_method = new Mcommerce_Model_Delivery_Method();
            $delivery_method->saveStoreDatas($this->getId(), $delivery_method_datas);
        }

        if($this->getNewPaymentMethods()) {
            $payment_method = new Mcommerce_Model_Payment_Method();
            $payment_method->saveStoreDatas($this->getId(), $this->getNewPaymentMethods());
        }

        return $this;

    }

    public function getFullAddress($separator = '<br />') {

        $address = array();
        if($this->getStreet()) $address[] = $this->getStreet($separator);
        if($this->getPostcode() AND $this->getCity()) $address[] = $this->getPostcode() . ' - ' . $this->getCity();
        if($this->getCountry()) $address[] = $this->getCountry();

        return join(', ', $address);
    }

    public function getStreet($separator = '<br />') {
        return str_replace('\r', $separator, $this->getData('street'));
    }

    public function getTaxes() {

        if(!$this->_taxes) {
            $tax = new Mcommerce_Model_Tax();
            $this->_taxes = $tax->findAll(array('mcommerce_id' => $this->getMcommerceId()));
//            $this->_taxes = $tax->findByStore($this->getId());
        }

        return $this->_taxes;

    }

    public function getTax($id) {
        $tax = new Mcommerce_Model_Tax();
        foreach($this->getTaxes() as $store_tax) {
            if($store_tax->getId() == $id) $tax = $store_tax;
        }
        return $tax;
    }

    public function getDeliveryMethods() {

        if(!$this->_delivery_methods) {
            $delivery_method = new Mcommerce_Model_Delivery_Method();
            $this->_delivery_methods = $delivery_method->findByStore($this->getId());
            foreach($this->_delivery_methods as $method) {
                $method->setStore($this);
            }
        }

        return $this->_delivery_methods;

    }

    public function getDeliveryMethod($method_id) {

        foreach($this->getDeliveryMethods() as $method) {
            if($method->getId() == $method_id) return $method;
        }

        return new Mcommerce_Model_Delivery_Method();
    }

    public function hasDeliveryMethod($id) {

        if(!$this->_delivery_method_ids) {
            $this->_delivery_method_ids = array();
            foreach($this->getDeliveryMethods() as $method) {
                $this->_delivery_method_ids[] = $method->getId();
            }
        }

        return in_array($id, $this->_delivery_method_ids);
    }

    public function getPaymentMethods() {

        if(!$this->_payment_methods) {
            $delivery_method = new Mcommerce_Model_Payment_Method();
            $this->_payment_methods = $delivery_method->findByStore($this->getId());
        }

        return $this->_payment_methods;

    }

    public function getPaymentMethod($method_id) {

        foreach($this->getPaymentMethods() as $method) {
            if((is_numeric($method_id) AND $method->getId() == $method_id)
                OR ($method->getCode() == $method_id)) return $method;
        }

        return new Mcommerce_Model_Payment_Method();
    }

    public function getPaymentMethodByCode($method_code) {

        foreach($this->getPaymentMethods() as $method) {
            if($method->getCode() == $method_code) return $method;
        }

        return new Mcommerce_Model_Payment_Method();
    }

    public function hasPaymentMethod($id) {

        if(!$this->_payment_method_ids) {
            $this->_payment_method_ids = array();
            foreach($this->getPaymentMethods() as $method) {
                $this->_payment_method_ids[] = $method->getId();
            }
        }

        return in_array($id, $this->_payment_method_ids);
    }

    public function getPrinter() {

        if(!$this->_printer) {
            $printer = new Mcommerce_Model_Store_Printer();
            $this->_printer = $printer->find($this->getId(), "store_id");
        }

        return $this->_printer;

    }


}
