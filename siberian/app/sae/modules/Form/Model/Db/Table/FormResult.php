<?php

/**
 * Class Form_Model_Db_Table_FormResult
 */
class Form_Model_Db_Table_FormResult extends Core_Model_Db_Table
{
    /**
     * @var string
     */
    protected $_name = "form_result";

    /**
     * @var string
     */
    protected $_primary = "result_id";

    /**
     * @param $valueId
     * @param $excludeAnonymous
     * @param $lastUserRecord
     * @return mixed
     */
    public function fetchForCsv ($valueId, $excludeAnonymous, $lastUserRecord)
    {
        $select = $this
            ->_db
            ->select()
            ->from("form_result","*")
            ->joinLeft("customer", "customer.customer_id = form_result.customer_id", ["firstname", "lastname", "email"])
            ->where("form_result.value_id = ?", $valueId);

        if ($excludeAnonymous) {
            $select->where("form_result.customer_id IS NOT NULL");
        }

        if ($lastUserRecord) {
            $select
                ->group("form_result.customer_id")
                ->order("form_result.created_at DESC");
        }

        return $this->toModelClass($this->_db->fetchAll($select));
    }
}