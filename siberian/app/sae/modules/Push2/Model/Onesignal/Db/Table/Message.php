<?php

namespace Push2\Model\Onesignal\Db\Table;

require_once path('/lib/onesignal/vendor/autoload.php');

use Core_Model_Db_Table as DbTable;

/**
 * Class Message
 * @package Push2\Model\Onesignal
 */
class Message extends DbTable
{
    public $_name = 'push2_message';
    public $_primary = 'message_id';
}