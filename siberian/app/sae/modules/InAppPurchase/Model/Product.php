<?php

namespace InAppPurchase\Model;

use Core\Model\Base;

/**
 * Class Product
 * @package InAppPurchase\Model
 */
class Product extends Base
{
    /**
     * @var string
     */
    protected $_db_table = InAppPurchase\Model\Db\Table\Product::class;
}
