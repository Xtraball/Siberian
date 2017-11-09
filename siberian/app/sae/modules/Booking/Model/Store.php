<?php
class Booking_Model_Store extends Core_Model_Default {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Booking_Model_Db_Table_Store';
        return $this;
    }

}
