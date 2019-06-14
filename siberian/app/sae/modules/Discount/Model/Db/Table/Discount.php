<?php

/**
 * Class Discount_Model_Db_Table_Discount
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.17.0
 */
class Discount_Model_Db_Table_Discount extends Core_Model_Db_Table
{
    /**
     * @var string
     */
    protected $_name = "promotion";
    /**
     * @var string
     */
    protected $_primary = "promotion_id";

    /**
     * @var string
     */
    protected $_modelClass = 'Promotion_Model_Promotion';

    /**
     * @param int $subDay
     * @return Zend_Db_Table_Rowset_Abstract
     * @throws Zend_Date_Exception
     */
    public function getOldPromotions($subDay = 30)
    {
        $date = new Zend_Date();
        $now = $date->toString('y-MM-dd');
        $date->subDay($subDay);
        $before = $date->toString('y-MM-dd');
        $where = join(' AND ', [
            $this->_db->quoteInto('end_at < ?', $now),
            $this->_db->quoteInto('end_at > ?', $before)
        ]);

        return $this->fetchAll($where);
    }

    /**
     * @param $start_at
     * @param $end_at
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getUsedPromotions($start_at, $end_at)
    {

        $select = $this->select()->setIntegrityCheck(false)
            ->from(['main' => $this->_name])
            ->join(['pc' => 'promotion_customer'], 'pc.promotion_id = main.promotion_id', ['pos_id', 'used_at' => 'created_at'])
            ->join(['c' => 'customer'], 'c.customer_id = pc.customer_id', ['customer_name' => new Zend_Db_Expr('CONCAT(c.firstname, " ", c.lastname)'), 'customer_mail' => "c.email"])
            ->where('pc.created_at >= ?', $start_at)
            ->where('pc.created_at <= ?', $end_at)
            ->group('pc.customer_id')
            ->group('pc.promotion_customer_id');

        return $this->fetchAll($select);

    }

    /**
     * @param $select
     * @param $customer_id
     * @return mixed
     */
    protected function removeUsedPromotions($select, $customer_id)
    {

        $select->joinLeft(['pc' => 'promotion_customer'],
            join(' AND ', [
                'p.promotion_id = pc.promotion_id',
                $this->_db->quoteInto('pc.customer_id = ?', $customer_id)
            ]),
            [])
            ->where('pc.promotion_id IS NULL');
        return $select;
    }


    /**
     * @return array
     */
    public function getAppIdByPromotionId()
    {
        $select = $this->select()
            ->from($this->_name, ['promotion_id'])
            ->joinLeft('application_option_value', $this->_name . '.value_id = application_option_value.value_id', 'app_id')
            ->setIntegrityCheck(false);
        return $this->_db->fetchAssoc($select);
    }

    /**
     * @param $app_id
     * @return array
     */
    public function findAllPromotionsByAppId($app_id)
    {
        $select = $this->select()
            ->from(['p' => $this->_name], ['promotion_id', 'title'])
            ->joinLeft(['aov' => 'application_option_value'], 'p.value_id = aov.value_id', 'app_id')
            ->where("aov.app_id = ?", $app_id)
            ->setIntegrityCheck(false);
        return $this->_db->fetchAssoc($select);
    }

    /**
     * @return mixed
     */
    public function fixPromotions()
    {
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