<?php

namespace InAppPurchase\Model\Db\Table;

use Core_Model_Db_Table as DbTable;

/**
 * Class Purchase
 * @package InAppPurchase\Model\Db\Table
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.19.0
 */
class Purchase extends DbTable
{
    /**
     * @var string
     */
    protected $_name = 'iap_purchase';

    /**
     * @var string
     */
    protected $_primary = 'purchase_id';

}
