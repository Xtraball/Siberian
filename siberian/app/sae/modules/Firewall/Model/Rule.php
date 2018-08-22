<?php

/**
 * Class Firewall_Model_Rule
 *
 * @method string getType()
 * @method string getValue()
 * @method $this setType(string $type)
 * @method $this setValue(string $value)
 * @method Firewall_Model_Rule[] findAll($values = [], $order = null, $params = [])
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
