<?php

class Promotion_Model_Db_Table_Promotion extends Core_Model_Db_Table
{
    protected $_name = "promotion";
    protected $_primary = "promotion_id";

    public function getOldPromotions($subDay = 30) {
        $date = new Zend_Date();
        $now = $date->toString('y-MM-dd');
        $date->subDay($subDay);
        $before = $date->toString('y-MM-dd');
        $where = join(' AND ', array(
            $this->_db->quoteInto('end_at < ?', $now),
            $this->_db->quoteInto('end_at > ?', $before)
        ));

        return $this->fetchAll($where);
    }

    public function getUsedPromotions($start_at, $end_at) {

        $select = $this->select()->setIntegrityCheck(false)
            ->from(array('main' => $this->_name))
            ->join(array('pc' => 'promotion_customer'), 'pc.promotion_id = main.promotion_id', array('pos_id', 'used_at' => 'created_at'))
            ->join(array('c' => 'customer'), 'c.customer_id = pc.customer_id', array('customer_name' => new Zend_Db_Expr('CONCAT(c.firstname, " ", c.lastname)')))
            ->where('pc.created_at >= ?', $start_at)
            ->where('pc.created_at <= ?', $end_at)
            ->group('pc.customer_id')
            ->group('pc.promotion_customer_id')
        ;

        return $this->fetchAll($select);

    }

    protected function removeUsedPromotions($select, $customer_id) {

        $select->joinLeft(array('pc' => 'promotion_customer'),
            join(' AND ', array(
                'p.promotion_id = pc.promotion_id',
                $this->_db->quoteInto('pc.customer_id = ?', $customer_id)
            )),
            array())
            ->where('pc.promotion_id IS NULL')
        ;
        return $select;
    }

}