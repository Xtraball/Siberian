<?php

namespace PaymentStripe\Model;

use Core\Model\Base;

/**
 * Class Log
 * @package PaymentStripe\Model
 */
class Log extends Base
{
    /**
     * @var string
     */
    protected $_db_table = Db\Table\Log::class;
}