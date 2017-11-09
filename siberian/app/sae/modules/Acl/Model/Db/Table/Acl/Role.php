<?php

class Acl_Model_Db_Table_Acl_Role extends Core_Model_Db_Table {

    protected $_name = "acl_role";
    protected $_primary = "role_id";

    public function findByRoleId($role_id) {
        $select = $this->select()
            ->from(array('c' => $this->_name))
            ->where('role_id = ?', $role_id);
        return $this->fetchRow($select);
    }

    public function saveResources($role_id, $resources) {

        $this->beginTransaction();

        try {

            $this->_db->delete('acl_resource_role', array('role_id = ?' => $role_id));

            foreach($resources as $resource) {
                if(!$resource["is_allowed"]) {
                    $data = array('resource_id' => $resource["id"], 'role_id' => $role_id);
                    $this->_db->insert('acl_resource_role', $data);
                }
            }

            $this->commit();
            return true;
        } catch(Exception $e) {
            $this->rollback();
            return false;
        }
    }

}