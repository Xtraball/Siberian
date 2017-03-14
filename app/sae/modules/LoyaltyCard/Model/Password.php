<?php

class LoyaltyCard_Model_Password extends Core_Model_Default
{

    public function __construct($datas = array()) {
        parent::__construct($datas);
        $this->_db_table = 'LoyaltyCard_Model_Db_Table_Password';
    }

    public function findByPassword($password, $value_id) {
        $this->find(array('password' => $this->_encrypt($password), 'value_id' => $value_id));
        return $this;
    }

    public function findByUnlockCode($password, $value_id) {
        $this->find(array('unlock_code' => $password, 'value_id' => $value_id));
        return $this;
    }

    protected function _encrypt($password) {
        return sha1($password);
    }

}