<?php

/**
 * Class Cms_Model_Application_Page_Block_Slider
 */
class Cms_Model_Application_Page_Block_Slider extends Cms_Model_Application_Page_Block_Image_Abstract {

    /**
     * Cms_Model_Application_Page_Block_Slider constructor.
     * @param array $params
     */
    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Cms_Model_Db_Table_Application_Page_Block_Slider';
        return $this;
    }

    /**
     * @return bool
     */
    public function isValid() {
        if($this->getLibraryId()) {
            return true;
        }

        return false;
    }
    
}