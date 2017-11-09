<?php

class Application_Model_Db_Table_Acl_Option extends Core_Model_Db_Table
{

    protected $_name = "application_acl_option";
    protected $_primary = "application_acl_option_id";

    public function findAllByAppId($app_id, $admin_id) {
        $select = $this->select()
            ->from(array('aao' => $this->_name))
            ->where('aao.app_id = ?', $app_id)
            ->where('aao.admin_id = ?', $admin_id);
        return $this->fetchAll($select);
    }

    public function findAllByAppAndAdminId($app_id, $admin_id) {
        $select = $this->select()
            ->from(array('aao' => $this->_name))
            ->where('aao.app_id = ?', $app_id)
            ->where('aao.admin_id = ?', $admin_id);
        return $this->fetchAll($select);
    }

    public function deleteAppAclByAdmin($app_id, $admin_id) {
        $this->_db->delete($this->_name, array('app_id = ?' => $app_id, 'admin_id = ?' => $admin_id));
    }
}