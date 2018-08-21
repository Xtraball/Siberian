<?php

/**
 * Class Firewall_Model_Rule
 */
class Firewall_Model_Rule extends Core_Model_Default
{
    const FW_TYPE_UPLOAD = 'fw_type_upload';

    /**
     * Firewal_Model_Rule constructor.
     * @param array $params
     * @throws Zend_Exception
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = 'Firewall_Model_Db_Table_Rule';
        return $this;
    }
}
