<?php

namespace Fanwall\Model\Db\Table;

use Core_Model_Db_Table as DbTable;

/**
 * Class BlockedUser
 * @package Fanwall\Model\Db\Table
 */
class BlockedUser extends DbTable
{
    /**
     * @var string
     */
    protected $_name = "fanwall_blocked_user";
    /**
     * @var string
     */
    protected $_primary = "blocked_user_id";
}