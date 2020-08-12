<?php

class Promotion_Model_Customer extends Promotion_Model_Promotion
{
    public function __construct($datas = array()) {
        parent::__construct($datas);
        $this->_db_table = 'Promotion_Model_Db_Table_Customer';
    }

    public function findAllByValue($value_id, $customer_id) {
        $rows = $this->getTable()->findAllByValue($value_id, $customer_id);
//        foreach($rows as $row) {
//
//        }
        return $rows;
    }

    public function addError() {

        if($this->getPromotionId() AND $this->getCustomerId()) {

            $now = $this->formatDate(null, 'y-MM-dd HH:mm:ss');
            $date = new Zend_Date($this->getLastError());
            $last_error = $date->addDay(1)->toString('y-MM-dd HH:mm:ss');
            if($last_error < $now) $nbr = 1;
            else $nbr = (int) $this->getNumberOfError() + 1;

            $this->setNumberOfError($nbr)->setLastError($now)->save();
            return $this;

        }
    }

    public function findLast($promotion_id, $customer_id) {
        $row = $this->getTable()->findLast($promotion_id, $customer_id);
        $this->unsetData();

        if($row) {
            $this->setData($row->getData())
                ->setId($row->getId())
            ;
        }

        return $this;
    }

    public function isLocked() {

        if(!$this->getId() OR $this->getNumberOfError() < 3) return false;

        $lastError = $this->getLastError();
        $now = new Zend_Date();
        $yesterday =  $now->subDay(1)->toString('y-MM-dd HH:mm:ss');

        return $lastError > $yesterday;

    }

}