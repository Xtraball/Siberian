<?php

namespace PaymentMethod\Model\Db\Table;

use Core_Model_Db_Table as DbTable;

/**
 * Class Payment
 * @package PaymentMethod\Model\Db\Table
 */
class Payment extends DbTable
{
    /**
     * @var string
     */
    protected $_name = "payment_method_payment";

    /**
     * @var string
     */
    protected $_primary = "payment_id";

}
