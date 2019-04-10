<?php

use Core\Model\Base;

/**
 * Class Form_Model_FormResult
 *
 * @method Form_Model_Db_Table_FormResult getTable()
 */
class Form_Model_FormResult extends Base
{
    /**
     * Form_Model_FormResult constructor.
     * @param array $params
     * @throws Zend_Exception
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = "Form_Model_Db_Table_FormResult";
        return $this;
    }

    /**
     * @param $valueId
     * @param $excludeAnonymous
     * @param $lastUserRecord
     * @return mixed
     */
    public function fetchForCsv ($valueId, $excludeAnonymous, $lastUserRecord)
    {
        return $this->getTable()->fetchForCsv($valueId, $excludeAnonymous, $lastUserRecord);
    }
}
