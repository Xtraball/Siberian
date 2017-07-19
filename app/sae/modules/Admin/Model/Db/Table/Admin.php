<?php

class Admin_Model_Db_Table_Admin extends Core_Model_Db_Table {

    protected $_name = "admin";
    protected $_primary = "admin_id";

    public function getStats() {


        $date = new Siberian_Date();
        $date->addDay(1);
        $endDate= $date->toString("yyyy-MM-dd HH:mm:ss");
        $date->setDay(1);
        $startDate = $date->toString("yyyy-MM-dd HH:mm:ss");

        //select MONTH(`created_at`), count(*) as count from admin group by MONTH(`created_at`)

        $select = $this->select()
            ->from($this->_name, array("count" => new Zend_Db_Expr("COUNT(admin_id)"), "day"=> new Zend_Db_Expr("DATE(created_at)")))
            ->where("created_at <= ?", $endDate)
            ->where("created_at > ?", $startDate)
            ->order("created_at")
            ->group("DATE(created_at)")
        ;



        return $this->fetchAll($select);

    }

    public function isAllowedToAddPages($admin_id, $app_id) {

        $select = $this->_db->select()
            ->from("application_admin", array("is_allowed_to_add_pages"))
            ->where("app_id = ?", $app_id)
            ->where("admin_id = ?", $admin_id);
        ;

        return $this->_db->fetchOne($select);

    }

    public function isAllowedToManageTour($admin_id) {

        $select = $this->_db->select()
            ->from($this->_name, array("is_allowed_to_manage_tour"))
            ->where("admin_id = ?", $admin_id);
        ;

        return $this->_db->fetchOne($select);

    }

    public function getAvailableRole() {
        $select = $this->_db->select()
            ->from("acl_role", array("role_id","code","label"))
        ;

        return $this->_db->fetchAll($select);
    }

    public function getAllApplicationAdmins($app_id) {
        $select = $this->_db->select()
            ->from(array("a" => $this->_name))
            ->joinLeft(array("aa" => "application_admin"), "a.admin_id = aa.admin_id", array("is_allowed_to_add_pages"))
            ->where("aa.app_id = ?", $app_id)
        ;

        return $this->_db->fetchAll($select);
    }

    public function getApplicationsByDesignType($type, $admin_id) {
        $select = $this->_db->select()
            ->from(array("a" => $this->_name))
            ->join(array("aa" => "application_admin"), "a.admin_id = aa.admin_id")
            ->join(array("app" => "application"), "app.app_id = aa.app_id", array(""))
            ->where("a.admin_id = ?", $admin_id)
            ->where("app.design_code = ?", $type)
        ;

        return $this->_db->fetchAll($select);
    }
}