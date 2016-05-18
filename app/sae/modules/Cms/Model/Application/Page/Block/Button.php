<?php

class Cms_Model_Application_Page_Block_Button extends Cms_Model_Application_Page_Block_Abstract {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Cms_Model_Db_Table_Application_Page_Block_Button';
        return $this;
    }
    
    public function isValid() {

        if($this->getContent()) {
            if ($this->getTypeId() == "link") {
                
            }

            return true;
        }
        if($this->getContent()) return true;
        return false;
    }
    
}