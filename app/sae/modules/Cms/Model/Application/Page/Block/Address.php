<?php

class Cms_Model_Application_Page_Block_Address extends Cms_Model_Application_Page_Block_Abstract
{

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Cms_Model_Db_Table_Application_Page_Block_Address';
        return $this;
    }

    public function isValid() {
        return !is_null($this->getAddress());
        return false;
    }

}