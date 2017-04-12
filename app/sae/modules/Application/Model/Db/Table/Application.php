<?php

class Application_Model_Db_Table_Application extends Core_Model_Db_Table
{
    protected $_name    =   "application";
    protected $_primary =   "app_id";

    public function findByHost($domain) {
        return $this->fetchRow($this->_db->quoteInto('domain = ?', $domain));
    }

    /**
     * @param $admin_id
     * @param array $where
     * @param null $order
     * @param null $count
     * @param null $offset
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function findAllByAdmin($admin_id, $where = array(), $order = null, $count = null, $offset = null) {
        $select = $this->select()
            ->from(array('a' => $this->_name))

            ->setIntegrityCheck(false)
        ;

        if($admin_id != null) {
            $select
                ->joinLeft(array('aa' => 'application_admin'), 'aa.app_id = a.app_id', array("is_allowed_to_add_pages"))
                ->where('aa.admin_id = ?', $admin_id);
        }

        if(!empty($where)) {
            $this->_where($select, $where);
        }

        if($order != null) {
            $this->_order($select, $order);
        }

        if($count != null) {
            $select->limit($count, $offset);
        }

        return $this->fetchAll($select);
    }

    public function findAllToPublish() {

        $status_id = Application_Model_Device::STATUS_PUBLISHED;

        $select = $this->_db->select()
            ->from(array("a" => $this->_name), array("app_id"))
            ->joinLeft(array("ad" => "application_device"), "ad.app_id = a.app_id")
            ->where("ad.status_id != ?", $status_id)
            ->group("a.app_id")
        ;

        return $this->_db->fetchCol($select);

    }

    public function getAdminIds($app_id) {

         $select = $this->_db->select()
            ->from(array('aa' => "application_admin"), array('admin_id'))
            ->where('aa.app_id = ?', $app_id)
        ;

        return $this->_db->fetchCol($select);
    }

    public function hasAsAdmin($app_id, $admin_id) {
        
        $select = $this->_db->select()
            ->from(array('aa' => "application_admin"), array('app_id'))
            ->where('aa.app_id = ?', $app_id)
            ->where('aa.admin_id = ?', $admin_id)
        ;

        return (bool) $this->_db->fetchOne($select);
    }

    public function addAdmin($app_id, $admin_id, $is_allowed_to_add_pages = true) {

        $admin_ids = $this->getAdminIds($app_id);
        
        if(!in_array($admin_id, $admin_ids)) {
            $this->_db->insert("application_admin", array("app_id" => $app_id, "admin_id" => $admin_id, "is_allowed_to_add_pages" => $is_allowed_to_add_pages));
        } else {
            $this->_db->update("application_admin", array("is_allowed_to_add_pages" => $is_allowed_to_add_pages), array("app_id = ?" => $app_id, "admin_id = ?" => $admin_id));
        }

        return $this;
    }

    public function removeAdmin($app_id, $admin_id) {
        $this->_db->delete("application_admin", array("app_id = ?" => $app_id, "admin_id = ?" => $admin_id));
        return $this;
    }

    public function updateOptionValuesPosition($positions) {

        foreach($positions as $pos => $option_value_id) {
            $this->_db->update($this->_name.'_option_value', array('position' => $pos), array('value_id = ?' => $option_value_id));
        }

    }

    public function isSomeoneElseEditingIt($app_id, $session_id, $admin_id) {

        $str = '%s:14:"editing_app_id";s:'.strlen($app_id).':"'.$app_id.'"%';
        $not_like_myself = '%s:9:object_id";s:'.strlen($admin_id).':"'.$admin_id.'%';
        
        $select = $this->_db->select()
            ->from("session")
            ->where("data LIKE ?", $str)
            ->where("data NOT LIKE ?", $not_like_myself)
            ->where("session_id != ?", $session_id)
            ->where("`modified` + 300 > ?", new Zend_Db_Expr("UNIX_TIMESTAMP()"))
        ;

        $cols = $this->_db->fetchCol($select);

        return count($cols) > 0;

    }

    /**
     * @return array
     */
    public function findAllForGlobalPush() {

        $request = "
SELECT DISTINCT({$this->_name}.{$this->_primary})
FROM {$this->_name}
INNER JOIN application_device ON {$this->_name}.app_id = application_device.app_id
WHERE application_device.status_id = 3
;";

        return $this->_db->fetchCol($request);

    }

}