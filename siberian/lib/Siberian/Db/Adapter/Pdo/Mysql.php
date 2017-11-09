<?php
/**
 * Extended Zend_Db_Adapter_Pdo_Mysql - try to reconnect if mysql gone away
 */
class Siberian_Db_Adapter_Pdo_Mysql extends Zend_Db_Adapter_Pdo_Mysql {

    const MAX_RECONNECT_COUNT = 3;

    public function query($sql, $bind = array()) {
        try {
            return parent::query($sql, $bind);
        } catch (Zend_Db_Statement_Exception $e) {
            $message = $e->getMessage();
            if ($message == 'SQLSTATE[HY000]: General error: 2006 MySQL server has gone away') {
                $this->_reconnect();
                return parent::query($sql, $bind);
            } else {
                throw $e;
            }
        }
    }

    private function _reconnect() {
        $this->_connection = null;
        for ($i = 1; $i <= self::MAX_RECONNECT_COUNT; $i++) {
            sleep(1);
            try {
                $this->_connect();
            } catch (Zend_Db_Adapter_Exception $e) {
                if ($i == self::MAX_RECONNECT_COUNT) {
                    throw $e;
                }
            }
            if ($this->_connection) {
                return ;
            }
        }
    }
}