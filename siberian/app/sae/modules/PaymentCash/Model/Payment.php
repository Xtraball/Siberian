<?php

namespace PaymentCash\Model;

use Core\Model\Base;

/**
 * Class Payment
 * @package PaymentCash\Model
 */
class Payment extends Base
{
    /**
     * @var string
     */
    protected $_db_table = Db\Table\Payment::class;

}