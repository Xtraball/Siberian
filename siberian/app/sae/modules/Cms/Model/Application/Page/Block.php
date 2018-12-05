<?php

/**
 * Class Cms_Model_Application_Page_Block
 */
class Cms_Model_Application_Page_Block extends Core_Model_Default
{

    /**
     * Cms_Model_Application_Page_Block constructor.
     * @param array $params
     * @throws Zend_Exception
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = 'Cms_Model_Db_Table_Application_Page_Block';
        return $this;
    }

}
