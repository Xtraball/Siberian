<?php

namespace Form2\Model\Db\Table;

use Core_Model_Db_Table as DbTable;

/**
 * Class Result
 * @package Form2\Model\Db\Table
 */
class Result extends DbTable
{
    /**
     * @var string
     */
    protected $_name = 'form2_result';

    /**
     * @var string
     */
    protected $_primary = 'result_id';

    /**
     * @param $valueId
     * @param $excludeAnonymous
     * @param $lastUserRecord
     * @return mixed
     * @throws \Zend_Exception
     */
    public function fetchForCsv ($valueId, $excludeAnonymous, $lastUserRecord)
    {
        $select = $this
            ->_db
            ->select()
            ->from('form2_result', '*')
            ->joinLeft('customer', 'customer.customer_id = form2_result.customer_id', ['firstname', 'lastname', 'email'])
            ->where('form2_result.value_id = ?', $valueId);

        if ($excludeAnonymous) {
            $select->where('form2_result.customer_id IS NOT NULL');
        }

        if ($lastUserRecord) {
            $select
                ->group('form2_result.customer_id')
                ->order('form2_result.created_at DESC');
        }

        return $this->toModelClass($this->_db->fetchAll($select));
    }
}