<?php

/**
 * Class Cms_Model_Application_Page_Block_Image_Library
 */
class Cms_Model_Application_Page_Block_Image_Library extends Core_Model_Default {

    /**
     * Cms_Model_Application_Page_Block_Image_Library constructor.
     * @param array $params
     */
    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Cms_Model_Db_Table_Application_Page_Block_Image_Library';
        return $this;
    }

    /**
     * @return string
     */
    public function getImage() {
        return $this->getImageUrl() ? Application_Model_Application::getImagePath().$this->getImageUrl() : "";
    }

    /**
     * @return string
     */
    public function getImageFullSize() {
        return $this->getImageFullsizeUrl() ? Application_Model_Application::getImagePath().$this->getImageFullsizeUrl() : "";
    }

    /**
     * @return mixed
     */
    public function findLastLibrary() {
        return $this->getTable()->findLastLibrary() + 1;
    }
}