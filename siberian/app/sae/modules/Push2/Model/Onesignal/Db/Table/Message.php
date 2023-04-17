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

    /**
     * @param $app_id
     * @param $player_id
     * @return mixed
     * @throws \Zend_Exception
     */
    public function findAllForPlayer($app_id, $player_id = null) {
        $select = <<<SQL
SELECT * 
FROM `{$this->_name}`
LEFT JOIN `push2_player_message` ON `push2_player_message`.`message_id` = `{$this->_name}`.`message_id` 
WHERE `app_id` = :app_id 
AND (
    (`is_individual` = 0)
    OR (`is_individual` = 1 AND `player_id` = :player_id)
)
AND `is_test` = 0
ORDER BY `{$this->_name}`.`created_at` DESC
SQL;

        dbg($select);

        return $this->toModelClass($this->_db->fetchAll($select, [
            'app_id' => $app_id,
            'player_id' => $player_id
        ]));
    }
}