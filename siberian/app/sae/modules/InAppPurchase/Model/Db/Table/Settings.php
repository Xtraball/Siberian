<?php

namespace InAppPurchase\Model\Db\Table;

use Core_Model_Db_Table as DbTable;

/**
 * Class Settings
 * @package InAppPurchase\Model\Db\Table
 *
 * @author Xtraball SAS <dev@xtraball.com>
 * @version 4.19.0
 */
class Settings extends DbTable
{
    /**
     * @var string
     */
    protected $_name = 'iap_settings';

    /**
     * @var string
     */
    protected $_primary = 'settings_id';

}
