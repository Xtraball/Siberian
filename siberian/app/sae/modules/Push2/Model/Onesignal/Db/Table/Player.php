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

    /**
     * @param $values
     * @param $order
     * @param $params
     * @return mixed
     * @throws \Zend_Exception
     */
    public function findWithCustomers($values, $order = null, $params = []) {
        $select = $this->_db->select()
            ->from(['player' => $this->_name])
            ->joinLeft(['customer' => 'customer'], 'customer.customer_id = player.customer_id', ['customer_id', 'firstname', 'lastname', 'email']);

        foreach ($values as $key => $value) {
            $select->where($key, $value);
        }

        $select->where("player.customer_id IS NOT NULL");

        return $this->toModelClass($this->_db->fetchAll($select));
    }
}