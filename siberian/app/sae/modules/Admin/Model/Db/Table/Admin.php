<?php

class Admin_Model_Db_Table_Admin extends Core_Model_Db_Table
{

    protected $_name = "admin";
    protected $_primary = "admin_id";

    public function getStats()
    {


        $date = new Siberian_Date();
        $date->addDay(1);
        $endDate = $date->toString("yyyy-MM-dd HH:mm:ss");
        $date->setDay(1);
        $startDate = $date->toString("yyyy-MM-dd HH:mm:ss");

        //select MONTH(`created_at`), count(*) as count from admin group by MONTH(`created_at`)

        $select = $this->select()
            ->from($this->_name, array("count" => new Zend_Db_Expr("COUNT(admin_id)"), "day" => new Zend_Db_Expr("DATE(created_at)")))
            ->where("created_at <= ?", $endDate)
            ->where("created_at > ?", $startDate)
            ->order("created_at")
            ->group("DATE(created_at)");


        return $this->fetchAll($select);

    }

    public function isAllowedToAddPages($admin_id, $app_id)
    {

        $select = $this->_db->select()
            ->from("application_admin", array("is_allowed_to_add_pages"))
            ->where("app_id = ?", $app_id)
            ->where("admin_id = ?", $admin_id);;

        return $this->_db->fetchOne($select);

    }

    public function isAllowedToManageTour($admin_id)
    {

        $select = $this->_db->select()
            ->from($this->_name, array("is_allowed_to_manage_tour"))
            ->where("admin_id = ?", $admin_id);;

        return $this->_db->fetchOne($select);

    }

    public function getAvailableRole()
    {
        $select = $this->_db->select()
            ->from("acl_role", array("role_id", "code", "label"));

        return $this->_db->fetchAll($select);
    }

    public function getAllApplicationAdmins($app_id)
    {
        $select = $this->_db->select()
            ->from(array("a" => $this->_name))
            ->joinLeft(array("aa" => "application_admin"), "a.admin_id = aa.admin_id", array("is_allowed_to_add_pages"))
            ->where("aa.app_id = ?", $app_id);

        return $this->_db->fetchAll($select);
    }

    public function getApplicationsByDesignType($type, $admin_id)
    {
        $select = $this->_db->select()
            ->from(array("a" => $this->_name))
            ->join(array("aa" => "application_admin"), "a.admin_id = aa.admin_id")
            ->join(array("app" => "application"), "app.app_id = aa.app_id", array(""))
            ->where("a.admin_id = ?", $admin_id)
            ->where("app.design_code = ?", $type);

        return $this->_db->fetchAll($select);
    }

    /**
     * @param $filters
     * @param null $order
     * @param array $params
     * @return mixed
     * @throws Zend_Db_Select_Exception
     * @throws Zend_Db_Statement_Exception
     * @throws Zend_Exception
     */
    public function findAllForBackoffice($filters, $order = null, $params = [])
    {
        $select = $this->_db->select()
            ->from(['a' => $this->_name], ['*']);

        $select->join(['r' => 'acl_role'], 'a.role_id = r.role_id', ['code', 'label']);
        $select->joinLeft(['pa' => $this->_name], 'a.parent_id = pa.admin_id', ['parent_firstname' => 'pa.firstname', 'parent_lastname' => 'pa.lastname']);

        foreach ($filters as $fKey => $fValue) {
            $select->where($fKey, $fValue);
        }

        if ($order !== null) {
            $select->order($order);
        }

        if (array_key_exists('offset', $params) && array_key_exists('limit', $params)) {
            $select->limit($params['limit'], $params['offset']);
        }

        return $this->toModelClass($this->_db->fetchAll($select));
    }

    /**
     * @param $filter
     * @return mixed
     * @throws Zend_Db_Select_Exception
     * @throws Zend_Db_Statement_Exception
     * @throws Zend_Exception
     */
    public function filterAdmins($filter)
    {
        $select = $this->_db->select()
            ->from($this->_name, ['*'])
            ->where('email LIKE ?', $filter)
            ->orWhere('firstname LIKE ?', $filter)
            ->orWhere('lastname LIKE ?', $filter)
            ->orWhere('company LIKE ?', $filter)
            ->orWhere('website LIKE ?', $filter)
            ->orWhere('phone LIKE ?', $filter);

        return $this->toModelClass($this->_db->fetchAll($select));
    }
}
