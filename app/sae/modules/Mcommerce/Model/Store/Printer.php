<?php

class Mcommerce_Model_Store_Printer extends Core_Model_Default {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Mcommerce_Model_Db_Table_Store_Printer';
        return $this;
    }

}
