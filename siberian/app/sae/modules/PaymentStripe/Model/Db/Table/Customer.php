<?php

namespace PaymentStripe\Model\Db\Table;

use Core_Model_Db_Table as DbTable;

/**
 * Class Customer
 * @package PaymentStripe\Model\Db\Table
 */
class Customer extends DbTable
{
    /**
     * @var string
     */
    protected $_name = 'payment_stripe_customer';

    /**
     * @var string
     */
    protected $_primary = 'stripe_customer_id';

}
