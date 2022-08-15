<?php

/**
 * Class Places_Model_CustomerNote
 *
 * @method integer getId()
 * @method Places_Model_Db_Table_CustomerNote getTable()
 */
class Places_Model_CustomerNote extends Core_Model_Default
{
    /**
     * @var string
     */
    protected $_db_table = Places_Model_Db_Table_CustomerNote::class;

    /**
     * @param $valueId
     * @param $placeId
     * @param $customerId
     * @return mixed
     * @throws Zend_Db_Select_Exception
     * @throws Zend_Db_Statement_Exception
     * @throws Zend_Exception
     */
    public function findNotes($valueId, $placeId, $customerId)
    {
        return $this->getTable()->findNotes($valueId, $placeId, $customerId);
    }
}