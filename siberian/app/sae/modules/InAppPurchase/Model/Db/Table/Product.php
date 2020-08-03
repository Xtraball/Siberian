<?php

namespace InAppPurchase\Model\Db\Table;

use Core_Model_Db_Table as DbTable;

/**
 * Class Product
 * @package InAppPurchase\Model\Db\Table
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.19.0
 */
class Product extends DbTable
{
    /**
     * @var string
     */
    protected $_name = 'iap_product';

    /**
     * @var string
     */
    protected $_primary = 'product_id';

}
