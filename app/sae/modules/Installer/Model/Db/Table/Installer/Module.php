<?php

class Installer_Model_Db_Table_Installer_Module extends Core_Model_Db_Table
{

    protected $_name = "module";
    protected $_primary = "module_id";
    protected $_is_installed = true;

    public function __construct($options = array()) {
        parent::__construct($options);
        try {
            $this->_db->describeTable($this->_name);
        }
        catch(Exception $e) {
            $this->_is_installed = false;
        }
        return $this;
    }

    public function isInstalled() {
        return $this->_is_installed;
    }

    public function install($module, $file, $version) {

        try {
            $this->start();
            require_once $file;
            $this->end();
        }
        catch(Exception $e) {
            $this->_db->rollback();
            if(APPLICATION_ENV != "production") {
                Zend_Debug::dump($e);
                die;
            } else {
                throw new Exception("An error occurred while upgrading the system. Please, contact your administrator.");
            }
        }
    }

    public function start() {
        $this->_db->beginTransaction();
    }

    public function end() {
        $this->_db->commit();
    }

    public function query($sql) {
        $this->_db->query($sql);
        return $this;
    }

}