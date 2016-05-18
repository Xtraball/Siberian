<?php

class Api_Model_User extends Core_Model_Default {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Api_Model_Db_Table_User';
        return $this;
    }

    public function setPassword($password) {
        if(strlen($password) < 6) throw new Exception($this->_('The password must be at least 6 characters'));
        $this->setData('password', $this->_encrypt($password));
        return $this;
    }

    public function isSamePassword($password) {
        return $this->getPassword() == $this->_encrypt($password);
    }

    public function authenticate($password) {
        return $this->_checkPassword($password);
    }

    private function _encrypt($password) {
        return sha1($password);
    }

    private function _checkPassword($password) {
        return $this->getPassword() == $this->_encrypt($password);
    }

}
