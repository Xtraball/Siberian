<?php

namespace Fanwall\Model\Db\Table;

use Core_Model_Db_Table as DbTable;

/**
 * Class Radius
 * @package Fanwall\Model\Db\Table
 */
class Radius extends DbTable
{
    /**
     * @var string
     */
    protected $_name = "fanwall_radius";

    /**
     * @var string
     */
    protected $_primary = "radius_id";

}