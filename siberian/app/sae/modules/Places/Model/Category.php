<?php

/**
 * Class Places_Model_Category
 */
class Places_Model_Category extends Core_Model_Default
{

    /**
     * Places_Model_Place constructor.
     * @param array $params
     * @throws Zend_Exception
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = 'Places_Model_Db_Table_Category';
        return $this;
    }
}