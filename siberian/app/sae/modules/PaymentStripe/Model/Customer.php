<?php

namespace PaymentStripe\Model;

use Core\Model\Base;

/**
 * Class Customer
 * @package PaymentStripe\Model
 */
class Customer extends Base
{
    /**
     * Customer constructor.
     * @param array $params
     * @throws \Zend_Exception
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = 'PaymentStripe\Model\Db\Table\Customer';
        return $this;
    }

    /**
     * @param $customerId
     * @throws \Zend_Exception
     */
    public static function getCustomer($customerId)
    {
        $customer = (new self())->find($customerId, "customer_id");
        if (!$customer->getId()) {
            $customer = new self();
            $customer
                ->setCustomerId($customerId)
                ->save();

            self::createCustomer($customerId);
        }
    }

    public static function createCustomer($customerId)
    {
        $stripeInstance = Application::getInstance();
    }
}