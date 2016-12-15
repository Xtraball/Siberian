<?php

class Mcommerce_Model_Promo_Log extends Core_Model_Default {

    public function __construct($datas = array()) {
        parent::__construct($datas);
        $this->_db_table = 'Mcommerce_Model_Db_Table_Promo_Log';
    }

    public static function createInstance($promo, $cart) {
        $log = new Mcommerce_Model_Promo_Log();
        $log->setPromoId($promo->getPromoId());
        $log->setCustomerIdentifier($cart->getCustomerId() ? $cart->getCustomerId() : $cart->getCustomerEmail());
        $log->setTotal($cart->getTotal());
        $log->setTtc(array_sum($cart->getTotalsFromLines()));
        $log->setDiscount($promo->getDeduction($cart));
        $log->setcode($cart->getDiscountCode());
        $log->setCustomerUuid($cart->getCustomerUUID());
        return $log;
    }
}