<?php

/**
 * Class Push_Model_DeliveredMessage
 */
class Push_Model_DeliveredMessage extends Core_Model_Default
{
    /**
     * Push_Model_DeliveredMessage constructor.
     * @param array $datas
     * @throws Zend_Exception
     */
    public function __construct($datas = [])
    {
        parent::__construct($datas);
        $this->_db_table = 'Push_Model_Db_Table_DeliveredMessage';
    }
}
