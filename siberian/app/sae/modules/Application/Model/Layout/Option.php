<?php

class Application_Model_Layout_Option extends Application_Model_Layout_Abstract {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Application_Model_Db_Table_Layout_Option';
        return $this;
    }

}
