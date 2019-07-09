<?php

/**
 * Class Discount_Model_Db_Table_Customer
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.17.0
 */
class Discount_Model_Db_Table_Customer extends Core_Model_Db_Table
{
    /**
     * @var string
     */
    protected $_name = "promotion_customer";
    /**
     * @var string
     */
    protected $_primary = "promotion_customer_id";

    /**
     * @param $value_id
     * @param $customer_id
     * @return Zend_Db_Table_Rowset_Abstract
     * @throws Zend_Date_Exception
     */
    public function findAllByValue($value_id, $customer_id)
    {

        $rows = [];

        $more_join = "";
        if ($customer_id > 0) {
            $more_join = ' AND pc.customer_id = ' . $customer_id;
        }

        $date = new Zend_Date();
        $select = $this->select();
        $select->from(['p' => 'promotion'])
            ->joinLeft(['pc' => 'promotion_customer'], 'pc.promotion_id = p.promotion_id' . $more_join, ['customer_id', 'is_used', 'used_at' => 'pc.created_at'])
//            ->joinLeft(array('pc' => 'promotion_customer'), 'pc.promotion_id = p.promotion_id AND pc.customer_id = '.$customer_id, array('customer_id', 'is_used' => new Zend_Db_Expr('IF(p.is_unique = 1, IF(pc.promotion_customer_id IS NULL, 0, 1), 0)'), 'used_at' => 'pc.created_at'))
            ->where('p.is_active = 1')
            ->where('p.value_id = ?', $value_id)
            ->where('p.end_at > ? OR p.end_at IS NULL', $date->toString('y-MM-dd'))
            ->where('p.condition_type IS NULL')
            ->group('p.promotion_id')
            ->order('p.promotion_id DESC')
            ->setIntegrityCheck(false);

        if ($customer_id != 0) {
            $select->where('pc.promotion_customer_id IS NULL OR pc.is_used = 0');
        }

        return $this->fetchAll($select);

    }

    /**
     * @param $promotion_id
     * @param $customer_id
     * @return Zend_Db_Table_Row_Abstract|null
     */
    public function findLast($promotion_id, $customer_id)
    {
        $select = $this->select()
            ->from($this->_name)
            ->where('promotion_id = ?', $promotion_id)
            ->where('customer_id = ?', $customer_id)
            ->where('is_used = 0')
            ->order('promotion_customer_id DESC')
            ->limit(1);

        return $this->fetchRow($select);
    }

}