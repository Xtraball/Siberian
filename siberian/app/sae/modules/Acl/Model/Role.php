<?php

class Acl_Model_Role extends Core_Model_Default {

    
    const DEFAULT_ROLE_ID = 1;

    const DEFAULT_ADMIN_ROLE_CODE = "admin_default_role_id";
    

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Acl_Model_Db_Table_Acl_Role';
        return $this;
    }

    public function save() {

        parent::save();

        if($resources = $this->getResources()) {
            $this->getTable()->saveResources($this->getId(), $resources);
        }

        return $this;
    }

    public function getRoleById($role_id) {
        $role = $this->getTable()->findByRoleId($role_id);
        if($role) {
            $this->setData($role->getData())->setId($role_id);
        }
        return $this;
    }

    public function findDefaultRoleId() {
        return System_Model_Config::getValueFor(self::DEFAULT_ADMIN_ROLE_CODE);
    }

    public function isDefaultRole() {
        if($this->getId()) {
            $data = $this->findDefaultRoleId();
            if($data == $this->getId()) return true;
        }
        return false;
    }


}