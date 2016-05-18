<?php

class Customer_Model_Address extends Core_Model_Default {

    public function __construct($datas = array()) {
        parent::__construct($datas);
        $this->_db_table = 'Customer_Model_Db_Table_Address';
    }

}