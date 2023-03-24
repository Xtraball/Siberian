<?php

use Siberian\Version;

/**
 * Class Application_Model_Db_Table_Application
 */
class Application_Model_Db_Table_Application extends Core_Model_Db_Table
{
    /**
     * @var string
     */
    protected $_name = "application";
    /**
     * @var string
     */
    protected $_primary = "app_id";

    /**
     * @param $domain
     * @return Zend_Db_Table_Row_Abstract|null
     */
    public function findByHost($domain)
    {
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
    public function findAllByAdmin($admin_id, $where = [], $order = null, $count = null, $offset = null)
    {
        $select = $this->select()
            ->from(
                [
                    "a" => $this->_name
                ],
                [
                    "creation_timestamp" => new \Zend_Db_Expr("UNIX_TIMESTAMP(a.created_at)"),
                    "free_until_timestamp" => new \Zend_Db_Expr("UNIX_TIMESTAMP(a.free_until)"),
                    "*"
                ])
            ->setIntegrityCheck(false);

        if ($admin_id != null) {
            $select
                ->joinLeft(['aa' => 'application_admin'], 'aa.app_id = a.app_id', ["is_allowed_to_add_pages"])
                ->where('aa.admin_id = ?', $admin_id);
        }

        if (!empty($where)) {
            $this->_where($select, $where);
        }

        if ($order != null) {
            $this->_order($select, $order);
        }

        if ($count != null) {
            $select->limit($count, $offset);
        }

        return $this->fetchAll($select);
    }

    /**
     * @return array
     */
    public function findAllToPublish()
    {

        $status_id = Application_Model_Device::STATUS_PUBLISHED;

        $select = $this->_db->select()
            ->from(["a" => $this->_name], ["app_id"])
            ->joinLeft(["ad" => "application_device"], "ad.app_id = a.app_id")
            ->where("ad.status_id != ?", $status_id)
            ->group("a.app_id");

        return $this->_db->fetchCol($select);

    }

    /**
     * @param $app_id
     * @return array
     */
    public function getAdminIds($app_id)
    {

        $select = $this->_db->select()
            ->from(['aa' => "application_admin"], ['admin_id'])
            ->where('aa.app_id = ?', $app_id);

        return $this->_db->fetchCol($select);
    }

    /**
     * @param $app_id
     * @param $admin_id
     * @return bool
     */
    public function hasAsAdmin($app_id, $admin_id)
    {

        $select = $this->_db->select()
            ->from(['aa' => "application_admin"], ['app_id'])
            ->where('aa.app_id = ?', $app_id)
            ->where('aa.admin_id = ?', $admin_id);

        return (bool) $this->_db->fetchOne($select);
    }

    /**
     * @param $app_id
     * @param $admin_id
     * @param bool $is_allowed_to_add_pages
     * @return $this
     * @throws Zend_Db_Adapter_Exception
     */
    public function addAdmin($app_id, $admin_id, $is_allowed_to_add_pages = true)
    {

        $admin_ids = $this->getAdminIds($app_id);

        if (!in_array($admin_id, $admin_ids)) {
            $this->_db->insert("application_admin", ["app_id" => $app_id, "admin_id" => $admin_id, "is_allowed_to_add_pages" => $is_allowed_to_add_pages]);
        } else {
            $this->_db->update("application_admin", ["is_allowed_to_add_pages" => $is_allowed_to_add_pages], ["app_id = ?" => $app_id, "admin_id = ?" => $admin_id]);
        }

        return $this;
    }

    /**
     * @param $app_id
     * @param $admin_id
     * @return $this
     */
    public function removeAdmin($app_id, $admin_id)
    {
        $this->_db->delete("application_admin", ["app_id = ?" => $app_id, "admin_id = ?" => $admin_id]);
        return $this;
    }

    /**
     * @param $positions
     * @throws Zend_Db_Adapter_Exception
     */
    public function updateOptionValuesPosition($positions)
    {

        foreach ($positions as $pos => $option_value_id) {
            $this->_db->update($this->_name . '_option_value', ['position' => $pos], ['value_id = ?' => $option_value_id]);
        }

    }

    /**
     * @param $app_id
     * @param $session_id
     * @param $admin_id
     * @return bool
     * @throws Zend_Db_Statement_Exception
     */
    public function isSomeoneElseEditingIt($app_id, $session_id, $admin_id)
    {
        return false;
    }

}
