<?php

/**
 * Class Places_Model_Db_Table_CustomerNote
 */
class Places_Model_Db_Table_CustomerNote extends Core_Model_Db_Table
{
    /**
     * @var string
     */
    protected $_name = 'place_customer_note';

    /**
     * @var string
     */
    protected $_primary = 'customer_note_id';

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
        $select = $this->_db->select()
            ->from(["note" => $this->_name], ["*", "time" => "UNIX_TIMESTAMP(note.created_at)"]);

        $select
            ->where("value_id = ?", $valueId)
            ->where("place_id = ?", $placeId)
            ->where("customer_id = ?", $customerId);

        $select->order("customer_note_id DESC");

        return $this->toModelClass($this->_db->fetchAll($select));
    }
}