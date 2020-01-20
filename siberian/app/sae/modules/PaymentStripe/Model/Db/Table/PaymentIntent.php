<?php

namespace PaymentStripe\Model\Db\Table;

use Core_Model_Db_Table as DbTable;

/**
 * Class PaymentIntent
 * @package PaymentStripe\Model\Db\Table
 */
class PaymentIntent extends DbTable
{
    /**
     * @var string
     */
    protected $_name = 'payment_stripe_payment_intent';

    /**
     * @var string
     */
    protected $_primary = 'stripe_payment_intent_id';
}
