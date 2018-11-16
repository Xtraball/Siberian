<?php

/**
 * Class Cms_Model_Application_Page_Block_Address
 */
class Cms_Model_Application_Page_Block_Address extends Cms_Model_Application_Page_Block_Abstract
{
    /**
     * Cms_Model_Application_Page_Block_Address constructor.
     * @param array $params
     * @throws Zend_Exception
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = 'Cms_Model_Db_Table_Application_Page_Block_Address';
        return $this;
    }

    /**
     * @return bool|mixed
     */
    public function isValid()
    {
        return !is_null($this->getAddress());
    }

}