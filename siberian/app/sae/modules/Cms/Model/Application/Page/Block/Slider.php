<?php

/**
 * Class Cms_Model_Application_Page_Block_Slider
 */
class Cms_Model_Application_Page_Block_Slider extends Cms_Model_Application_Page_Block_Image_Abstract
{
    /**
     * Cms_Model_Application_Page_Block_Slider constructor.
     * @param array $params
     * @throws Zend_Exception
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = 'Cms_Model_Db_Table_Application_Page_Block_Slider';
        return $this;
    }

    /**
     * @return bool|mixed
     */
    public function isValid()
    {
        if ($this->getLibraryId()) {
            return true;
        }

        return false;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function populate($data = []) {
        $this->setAllowLineReturn($data['allow_line_return']);

        parent::populate($data);

        return $this;
    }

}