<?php

class Promotion_Model_Db_Table_Promotion extends Core_Model_Db_Table
{
    protected $_name = "promotion";
    protected $_primary = "promotion_id";

    protected $_modelClass = 'Promotion_Model_Promotion';

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
            ->join(array('c' => 'customer'), 'c.customer_id = pc.customer_id', array('customer_name' => new Zend_Db_Expr('CONCAT(c.firstname, " ", c.lastname)'), 'customer_mail' => "c.email"))
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


    public function getAppIdByPromotionId() {
        $select = $this->select()
            ->from($this->_name, array('promotion_id'))
            ->joinLeft('application_option_value',$this->_name.'.value_id = application_option_value.value_id','app_id')
            ->setIntegrityCheck(false);
        return $this->_db->fetchAssoc($select);
    }

    public function findAllPromotionsByAppId($app_id) {
        $select = $this->select()
            ->from(array('p' => $this->_name), array('promotion_id', 'title'))
            ->joinLeft(array('aov' => 'application_option_value'),'p.value_id = aov.value_id','app_id')
            ->where("aov.app_id = ?", $app_id)
            ->setIntegrityCheck(false);
        return $this->_db->fetchAssoc($select);
    }

    public function fixPromotions() {
        $select = "SELECT 
promotion.*, 
application_option_value.app_id AS app_id
FROM promotion
INNER JOIN application_option_value ON application_option_value.value_id = promotion.value_id
WHERE promotion.unlock_by = 'qrcode'
AND ( 
  promotion.unlock_code IS NULL
  OR 
  promotion.unlock_code = ''
)";

        return $this->toModelClass($this->_db->fetchAll($select));
    }

}