<?php

namespace PaymentStripe\Model\Db\Table;

use Core_Model_Db_Table as DbTable;

/**
 * Class PaymentMethod
 * @package PaymentStripe\Model\Db\Table
 */
class PaymentMethod extends DbTable
{
    /**
     * @var string
     */
    protected $_name = "payment_stripe_payment_method";

    /**
     * @var string
     */
    protected $_primary = "stripe_payment_method_id";

    /**
     * @param $adminId
     * @param array $values
     * @return mixed
     * @throws \Zend_Exception
     */
    public function getForAdminId ($adminId, $values = [])
    {
        $select = $this->_db
            ->select()
            ->from("payment_stripe_payment_method")
            ->join("payment_stripe_customer", "payment_stripe_customer.stripe_customer_id = payment_stripe_payment_method.stripe_customer_id")
            ->where("payment_stripe_customer.admin_id = ?", $adminId);

        foreach ($values as $query => $binding) {
            $select->where($query, $binding);
        }

        return $this->toModelClass($this->_db->fetchAll($select));
    }

    /**
     * @param $customerId
     * @param array $values
     * @return mixed
     * @throws \Zend_Exception
     */
    public function getForCustomerId ($customerId, $values = [])
    {
        $select = $this->_db
            ->select()
            ->from("payment_stripe_payment_method")
            ->join("payment_stripe_customer", "payment_stripe_customer.stripe_customer_id = payment_stripe_payment_method.stripe_customer_id")
            ->where("payment_stripe_customer.customer_id = ?", $customerId);

        foreach ($values as $query => $binding) {
            $select->where($query, $binding);
        }

        return $this->toModelClass($this->_db->fetchAll($select));
    }
}
