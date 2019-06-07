<?php

class Job_Model_PlaceContact extends Core_Model_Default {

    public function __construct($params = []) {
        parent::__construct($params);
        $this->_db_table = 'Job_Model_Db_Table_PlaceContact';
        return $this;
    }
}