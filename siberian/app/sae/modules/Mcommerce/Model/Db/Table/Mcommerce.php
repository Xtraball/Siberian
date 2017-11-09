<?php

class Mcommerce_Model_Db_Table_Mcommerce extends Core_Model_Db_Table {

    protected $_name    = "mcommerce";
    protected $_primary = "mcommerce_id";

    public function getAppIdByMcommerceId() {
        $select = $this->select()
            ->from($this->_name, array('mcommerce_id'))
            ->joinLeft('application_option_value',$this->_name.'.value_id = application_option_value.value_id','app_id')
            ->setIntegrityCheck(false);
        return $this->_db->fetchAssoc($select);
    }
}
