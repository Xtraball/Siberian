<?php

namespace PaymentCash\Model\Db\Table;

use Core_Model_Db_Table as DbTable;

/**
 * Class Application
 * @package PaymentCash\Model\Db\Table
 */
class Application extends DbTable
{
    /**
     * @var string
     */
    protected $_name = "payment_cash_application";

    /**
     * @var string
     */
    protected $_primary = "cash_application_id";

}
