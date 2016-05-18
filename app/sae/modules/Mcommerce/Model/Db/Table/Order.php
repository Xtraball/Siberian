<?php

class Mcommerce_Model_Db_Table_Order extends Core_Model_Db_Table {

    protected $_name    = "mcommerce_order";
    protected $_primary = "order_id";

    public function getNextIncrement() {

//        $select = $this->_db->select()
//            ->from($this->_name, array('last_increment' => new Zend_Db_Expr('SUBSTRING_INDEX(number, "-BC", -1)')))
//            ->where('DATE(created_at) = ?', Siberian_Date::now()->toString('y-MM-dd'))
//            ->order('order_id DESC')
//            ->limit(1)
//        ;
//
//        return ((int) $this->_db->fetchOne($select)) + 1;

    }

}
