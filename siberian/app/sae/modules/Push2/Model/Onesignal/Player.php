<?php

namespace Push2\Model\Onesignal;

require_once path('/lib/onesignal/vendor/autoload.php');

use Core_Model_Default as BaseModel;

/**
 * Class Player
 * @package Push2\Model\Onesignal
 *
 * @method Db\Table\Player getTable()
 */
class Player extends BaseModel
{
    public $_db_table = Db\Table\Player::class;

    /**
     * @param $values
     * @param $order
     * @param $params
     * @return mixed
     * @throws \Zend_Exception
     */
    public function findWithCustomers($values, $order = null, $params = []) {
        return $this->getTable()->findWithCustomers($values, $order, $params);
    }
}