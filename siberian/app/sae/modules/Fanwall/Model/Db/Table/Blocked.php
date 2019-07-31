<?php

namespace Fanwall\Model\Db\Table;

use Core_Model_Db_Table as DbTable;

/**
 * Class Blocked
 * @package Fanwall\Model\Db\Table
 */
class Blocked extends DbTable
{
    /**
     * @var string
     */
    protected $_name = "fanwall_blocked";
    /**
     * @var string
     */
    protected $_primary = "blocked_id";
}