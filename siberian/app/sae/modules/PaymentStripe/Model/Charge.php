<?php

namespace PaymentStripe\Model;

use Core\Model\Base;

/**
 * Class Charge
 * @package PaymentStripe\Model
 */
class Charge extends Base
{
    /**
     * @var string
     */
    protected $_db_table = Db\Table\Charge::class;
}