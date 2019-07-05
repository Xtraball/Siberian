<?php

/**
 * Class Padlock_Model_Value
 */
class Padlock_Model_Value extends Core_Model_Default
{

    /**
     * @var null
     */
    protected $_value_ids = null;

    /**
     * Padlock_Model_Value constructor.
     * @param array $params
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = "Padlock_Model_Db_Table_Value";
        return $this;
    }

}
