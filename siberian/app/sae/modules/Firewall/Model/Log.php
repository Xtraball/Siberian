<?php

/**
 * Class Firewall_Model_Log
 */
class Firewall_Model_Log extends Core_Model_Default
{
    /**
     * Firewal_Model_Rule constructor.
     * @param array $params
     * @throws Zend_Exception
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = 'Firewall_Model_Db_Table_Log';
        return $this;
    }
}
