<?php

class Cms_Model_Application_Page_Block extends Core_Model_Default {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Cms_Model_Db_Table_Application_Page_Block';
        return $this;
    }

}
