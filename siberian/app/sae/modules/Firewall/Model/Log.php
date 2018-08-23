<?php

/**
 * Class Firewall_Model_Log
 *
 * @method string getType()
 * @method string getMessage()
 * @method string getUserClass()
 * @method integer getUserId()
 * @method datetime getCreatedAt()
 * @method Firewall_Model_Log[] findAll($values = [], $order = null, $params = [])
 */
class Firewall_Model_Log extends Core_Model_Default
{
    /**
     * Firewal_Model_Rule constructor.
     * @param array $params
     * @throws Zend_Exception
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = 'Firewall_Model_Db_Table_Log';
        return $this;
    }

    /**
     * @return Admin_Model_Admin|Backoffice_Model_User
     */
    public function getUser ()
    {
        $user = null;

        switch ($this->getUserClass()) {
            case 'Admin_Model_Admin':
                    $user = (new \Admin_Model_Admin())->find($this->getUserId());
                break;
            case 'Backoffice_Model_User':
                    $user = (new \Backoffice_Model_User())->find($this->getUserId());
                break;
        }

        return $user;
    }
}
