<?php

namespace Push2\Model\Onesignal\Db\Table;

use Core_Model_Db_Table as DbTable;

/**
 * Class PlayerMessage
 * @package Push2\Model\Onesignal
 */
class PlayerMessage extends DbTable
{
    protected $_name = 'push2_player_message';
    protected $_primary = 'player_message_id';
}