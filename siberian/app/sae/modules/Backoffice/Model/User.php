<?php

/**
 * Class Backoffice_Model_User
 */
class Backoffice_Model_User extends Core_Model_Default
{
    /**
     * Backoffice_Model_User constructor.
     * @param array $datas
     */
    public function __construct($datas = [])
    {
        parent::__construct($datas);
        $this->_db_table = 'Backoffice_Model_Db_Table_User';
    }

    /**
     * @param $password
     * @param int $min_length
     * @return $this
     * @throws Zend_Exception
     */
    public function setPassword($password, $min_length = 9)
    {
        return set_password_object($this, $password, $min_length);
    }

    /**
     * @param $password
     * @return bool
     */
    public function isSamePassword($password)
    {
        return $this->getPassword() == $this->_encrypt($password);
    }

    /**
     * @param $password
     * @return bool
     */
    public function authenticate($password)
    {
        return $this->_checkPassword($password);
    }

    /**
     * @param $password
     * @return string
     */
    private function _encrypt($password)
    {
        return sha1($password);
    }

    /**
     * @param $password
     * @return bool
     */
    private function _checkPassword($password)
    {
        return $this->getPassword() == $this->_encrypt($password);
    }

}