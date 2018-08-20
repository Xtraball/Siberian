<?php

/**
 * Class Application_Model_Db_Table_Acl_Option
 */
class Application_Model_Db_Table_Acl_Option extends Core_Model_Db_Table
{

    /**
     * @var string
     */
    protected $_name = "application_acl_option";

    /**
     * @var string
     */
    protected $_primary = "application_acl_option_id";

    /**
     * @param $app_id
     * @param $admin_id
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function findAllByAppId($app_id, $admin_id)
    {
        $select = $this->select()
            ->from(array('aao' => $this->_name))
            ->where('aao.app_id = ?', $app_id)
            ->where('aao.admin_id = ?', $admin_id);
        return $this->fetchAll($select);
    }

    /**
     * @param $app_id
     * @param $admin_id
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function findAllByAppAndAdminId($app_id, $admin_id)
    {
        $select = $this->select()
            ->from(array('aao' => $this->_name))
            ->where('aao.app_id = ?', $app_id)
            ->where('aao.admin_id = ?', $admin_id);
        return $this->fetchAll($select);
    }

    /**
     * @param $app_id
     * @param $admin_id
     */
    public function deleteAppAclByAdmin($app_id, $admin_id)
    {
        $this->_db->delete($this->_name, array('app_id = ?' => $app_id, 'admin_id = ?' => $admin_id));
    }
}