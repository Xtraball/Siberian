<?php

class Api_Model_User extends Core_Model_Default {

    /**
     * Api_Model_User constructor.
     * @param array $params
     */
    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Api_Model_Db_Table_User';
        return $this;
    }

    /**
     * @param $access_key
     * @return bool
     */
    public function hasAccess($access_key) {
        $parts = explode(".", $access_key);
        $acl = Siberian_Json::decode($this->getAcl());
        foreach($parts as $level) {
            if(isset($acl[$level])) {
                if(is_array($acl[$level])) {
                    $acl = $acl[$level];
                } elseif($acl[$level] === true) {
                    return true;
                }
            } else {
                return false;
            }
        }

        return false;
    }

    /**
     * @param $password
     * @return $this
     * @throws Exception
     */
    public function setPassword($password) {
        if(strlen($password) < 6) {
            throw new Exception(__("The password must be at least 6 characters"));
        }
        $this->setData("password", $this->_encrypt($password));
        return $this;
    }

    /**
     * @param $password
     * @return bool
     */
    public function isSamePassword($password) {
        return $this->getPassword() == $this->_encrypt($password);
    }

    /**
     * @param $password
     * @return bool
     */
    public function authenticate($password) {
        return $this->_checkPassword($password);
    }

    /**
     * @param $password
     * @return string
     */
    private function _encrypt($password) {
        return sha1($password);
    }

    /**
     * @param $password
     * @return bool
     */
    private function _checkPassword($password) {
        return ($this->getPassword() == $this->_encrypt($password));
    }

}
