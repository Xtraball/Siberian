<?php

/**
 * Class Cms_Model_Application_Page_Block_Address
 */
class Cms_Model_Application_Page_Block_Address extends Cms_Model_Application_Page_Block_Abstract
{
    /**
     * @var string
     */
    protected $_db_table = Cms_Model_Db_Table_Application_Page_Block_Address::class;

    /**
     * @return bool|mixed
     */
    public function isValid()
    {
        return !empty($this->getAddress());
    }

}