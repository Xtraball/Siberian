<?php

/**
 * Class Places_Model_PlaceCategory
 */
class Places_Model_PageCategory extends Core_Model_Default
{
    /**
     * Places_Model_PageCategory constructor.
     * @param array $params
     * @throws Zend_Exception
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = 'Places_Model_Db_Table_PageCategory';
        return $this;
    }
}