<?php

namespace PaymentStripe\Model\Db\Table;

use Core_Model_Db_Table as DbTable;

/**
 * Class Application
 * @package PaymentStripe\Model\Db\Table
 */
class Application extends DbTable
{
    /**
     * @var string
     */
    protected $_name = 'payment_stripe_application';

    /**
     * @var string
     */
    protected $_primary = 'stripe_application_id';

}