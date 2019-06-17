<?php

namespace Fanwall\Model\Db\Table;

use Core_Model_Db_Table as DbTable;

/**
 * Class Fanwall
 * @package Fanwall\Model\Db\Table
 */
class Fanwall extends DbTable
{
    /**
     * @var string
     */
    protected $_name = "fanwall";

    /**
     * @var string
     */
    protected $_primary = "fanwall_id";

}