<?php

namespace PaymentStripe\Model\Db\Table;

use Core_Model_Db_Table as DbTable;

/**
 * Class Log
 * @package PaymentStripe\Model\Db\Table
 */
class Log extends DbTable
{
    /**
     * @var string
     */
    protected $_name = 'payment_stripe_log';

    /**
     * @var string
     */
    protected $_primary = 'stripe_log_id';

}
