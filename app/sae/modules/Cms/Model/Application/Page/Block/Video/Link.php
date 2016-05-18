<?php

class Cms_Model_Application_Page_Block_Video_Link extends Cms_Model_Application_Page_Block_Abstract
{

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Cms_Model_Db_Table_Application_Page_Block_Video_Link';
        return $this;
    }

    public function isValid() {
        if($this->getLink()) return true;
        return false;
    }

    public function getImageUrl() {
        return $this->getImage() ? Application_Model_Application::PATH_IMAGE.$this->getImage() : null;
    }

}