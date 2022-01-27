<?php

namespace PaymentStripe\Model\Db\Table;

use Core_Model_Db_Table as DbTable;

/**
 * Class Charge
 * @package PaymentStripe\Model\Db\Table
 */
class Charge extends DbTable
{
    /**
     * @var string
     */
    protected $_name = 'payment_stripe_charge';

    /**
     * @var string
     */
    protected $_primary = 'stripe_charge_id';

}
