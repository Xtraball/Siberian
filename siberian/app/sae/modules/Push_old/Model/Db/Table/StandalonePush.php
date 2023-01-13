<?php

namespace Push\Model\Db\Table;

use Core_Model_Db_Table as DbTable;

/**
 * Class StandalonePush
 * @package Push\Model\Db\Table
 */
class StandalonePush extends DbTable
{
    /**
     * @var string
     */
    protected $_name = "standalone_push";

    /**
     * @var string
     */
    protected $_primary = "push_id";
}
