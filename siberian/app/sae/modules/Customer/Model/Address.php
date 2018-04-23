<?php

/**
 * Class Customer_Model_Address
 */
class Customer_Model_Address extends Core_Model_Default
{
    /**
     * Customer_Model_Address constructor.
     * @param array $datas
     */
    public function __construct($datas = [])
    {
        parent::__construct($datas);
        $this->_db_table = 'Customer_Model_Db_Table_Address';
    }

}