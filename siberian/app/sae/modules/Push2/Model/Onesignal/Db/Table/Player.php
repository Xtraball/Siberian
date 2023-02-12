<?php

namespace Push2\Model\Onesignal\Db\Table;

require_once path('/lib/onesignal/vendor/autoload.php');

use Core_Model_Db_Table as DbTable;

/**
 * Class Player
 * @package Push2\Model\Onesignal
 */
class Player extends DbTable
{
    protected $_name = 'push2_onesignal_player';
    protected $_primary = 'onesignal_player_id';
}