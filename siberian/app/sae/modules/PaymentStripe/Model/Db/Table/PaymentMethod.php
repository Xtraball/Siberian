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

}
