<?php

class Application_Model_Layout_Category extends Core_Model_Default {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Application_Model_Db_Table_Layout_Category';
        return $this;
    }

}
