<?php

class Acl_Model_Db_Table_Acl_Resource extends Core_Model_Db_Table {

    protected $_name = "acl_resource";
    protected $_primary = "resource_id";

    public function getResourceCodes() {
        $select = $this->_db->select()
            ->from($this->_name, array("code"))
        ;

        return $this->_db->fetchCol($select);
    }

    public function findResourcesByRole($role_id) {
        $select = $this->_db->select()
            ->from(array('ar' => $this->_name), array("code"))
            ->join(array('arr' => 'acl_resource_role'),"ar.resource_id = arr.resource_id", array())
            ->where('arr.role_id = ?', $role_id);
        return $this->_db->fetchCol($select);
    }

    public function findAllParents() {
        $select = $this->select()
            ->from(array('a' => $this->_name))
            ->where('a.parent_id IS NULL');
        return $this->fetchAll($select);
    }

    public function findByParentId($parent_id) {
        $select = $this->select()
            ->from(array('a' => $this->_name))
            ->where('parent_id = ?',$parent_id);
        return $this->fetchAll($select);
    }

    public function getUrlByCode($resources = null) {
        $select = $this->select()
            ->from(array('a' => $this->_name), array("code","url"))
            ->where('a.url IS NOT NULL')
            ->where('a.code IN (?)', $resources);
        return $this->fetchAll($select);
    }

    public function findByCode($code = null) {
        if($code) {
            $select = $this->select()
                ->from(array('a' => $this->_name))
                ->where('code = ?',$code);
            return $this->fetchRow($select);
        } else {
            return null;
        }
    }
}