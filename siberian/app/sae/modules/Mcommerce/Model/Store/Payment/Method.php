<?php

/**
 * Class Mcommerce_Model_Store_Payment_Method
 */
class Mcommerce_Model_Store_Payment_Method extends Core_Model_Default {
    /**
     * Mcommerce_Model_Store_Payment_Method constructor.
     * @param array $params
     */
    public function __construct($params = []) {
        parent::__construct($params);
        $this->_db_table = 'Mcommerce_Model_Db_Table_Store_Payment_Method';
        return $this;
    }
}
