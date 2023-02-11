<?php

namespace Push2\Model\Onesignal;

require_once path('/lib/onesignal/vendor/autoload.php');

use Core_Model_Default as BaseModel;

/**
 * Class Message
 * @package Push2\Model\Onesignal
 *
 * @method Db\Table\Player getTable()
 */
class Player extends BaseModel
{
    public $_db_table = Db\Table\Player::class;
}