<?php

class Application_Model_Acl_Option extends Core_Model_Default
{

    public function __construct($datas = array()) {
        parent::__construct($datas);
        $this->_db_table = 'Application_Model_Db_Table_Acl_Option';
    }

    public function findAllByAppId($app_id, $admin_id) {
        return $this->getTable()->findAllByAppId($app_id, $admin_id);
    }

    public function saveAccess($data) {
        return $this->getTable()->saveAccess($data);
    }

    public function findAllByAppAndAdminId($app_id, $admin_id) {
        $result = array();
        $collection =  $this->getTable()->findAllByAppAndAdminId($app_id, $admin_id);
        foreach($collection as $object) {
            $result[$object->getValueId()] = $object->getResourceCode();
        }
        return $result;
    }

    public function deleteAppAclByAdmin($app_id, $admin_id) {
        return $this->getTable()->deleteAppAclByAdmin($app_id, $admin_id);
    }

}