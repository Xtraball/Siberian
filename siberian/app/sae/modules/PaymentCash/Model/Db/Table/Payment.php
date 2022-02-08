<?php

namespace PaymentCash\Model\Db\Table;

use Core_Model_Db_Table as DbTable;

/**
 * Class Payment
 * @package PaymentCash\Model\Db\Table
 */
class Payment extends DbTable
{
    /**
     * @var string
     */
    protected $_name = 'payment_cash_payment';

    /**
     * @var string
     */
    protected $_primary = 'cash_payment_id';

}
