<?php

class LoyaltyCard_Model_Password extends Core_Model_Default
{

    public function __construct($datas = array()) {
        parent::__construct($datas);
        $this->_db_table = 'LoyaltyCard_Model_Db_Table_Password';
    }

    public function findByPassword($password, $app_id) {
        $this->find(array('password' => $this->_encrypt($password), 'app_id' => $app_id));
        return $this;
    }

    protected function _encrypt($password) {
        return sha1($password);
    }

}