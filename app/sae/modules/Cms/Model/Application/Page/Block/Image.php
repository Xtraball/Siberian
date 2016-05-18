<?php

class Cms_Model_Application_Page_Block_Image extends Cms_Model_Application_Page_Block_Abstract
{

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Cms_Model_Db_Table_Application_Page_Block_Image';
        return $this;
    }
    
    public function isValid() {
        if($this->getLibraryId()) return true;
        return false;
    }
    
}