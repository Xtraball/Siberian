<?php

namespace InAppPurchase\Model;

use Core\Model\Base;

/**
 * Class Purchase
 * @package InAppPurchase\Model
 */
class Purchase extends Base
{
    /**
     * @var string
     */
    protected $_db_table = Db\Table\Purchase::class;
}
