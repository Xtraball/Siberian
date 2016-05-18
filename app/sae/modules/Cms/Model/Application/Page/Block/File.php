<?php

class Cms_Model_Application_Page_Block_File extends Cms_Model_Application_Page_Block_Abstract {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Cms_Model_Db_Table_Application_Page_Block_File';
        return $this;
    }
    
    public function isValid() {

        if($this->getName()) {
            return true;
        }
        return false;
    }
    
}