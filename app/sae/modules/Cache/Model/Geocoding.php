<?php
class Cache_Model_Geocoding extends Core_Model_Default {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Cache_Model_Db_Table_Geocoding';
        return $this;
    }
}
