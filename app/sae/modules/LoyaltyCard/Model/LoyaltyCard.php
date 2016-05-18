<?php

class LoyaltyCard_Model_LoyaltyCard extends Core_Model_Default
{

    protected $_action_view = "findall";

    public function __construct($datas = array()) {
        parent::__construct($datas);
        $this->_db_table = 'LoyaltyCard_Model_Db_Table_LoyaltyCard';
    }

    public function findByValueId($value_id) {
        return $this->getTable()->findByValueId($value_id);
    }

    public function findLast($value_id) {
        return $this->getTable()->findLast($value_id);
    }

    public function copyTo($option) {
        $this->setId(null)->setValueId($option->getId())->save();
        return $this;
    }
}