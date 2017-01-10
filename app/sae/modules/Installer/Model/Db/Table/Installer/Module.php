<?php

class Installer_Model_Db_Table_Installer_Module extends Core_Model_Db_Table {

    /**
     * @var string
     */
    protected $_name = "module";

    /**
     * @var string
     */
    protected $_primary = "module_id";

    /**
     * @var bool
     */
    protected $_is_installed = true;

    /**
     * @var mixed
     */
    protected $_logger;

    /**
     * Installer_Model_Db_Table_Installer_Module constructor.
     * @param array $options
     */
    public function __construct($options = array()) {
        $this->_logger = Zend_Registry::get("logger");

        parent::__construct($options);
        try {
            $this->_db->describeTable($this->_name);
        }  catch(Exception $e) {
            $this->_is_installed = false;
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function isInstalled() {
        return $this->_is_installed;
    }

    /**
     * @param $module
     * @param $file
     * @throws Exception
     */
    public function install($module, $file) {

        try {
            $this->start();
            $this->query("SET foreign_key_checks = 0;");
            require_once $file;
            $this->query("SET foreign_key_checks = 1;");
            $this->end();
        }
        catch(Exception $e) {
            $this->_db->rollback();
            $this->query("SET foreign_key_checks = 1;");
            if(APPLICATION_ENV != "production") {
                Zend_Debug::dump($e);
                die;
            } else {
                throw new Exception("An error occurred while upgrading the system. Please, contact your administrator.");
            }
        }
    }

    /**
     * Starts a DB Transaction
     */
    public function start() {
        $this->_db->beginTransaction();
    }

    /**
     * Ends a DB Transaction
     */
    public function end() {
        $this->_db->commit();
    }

    /**
     * alias for direct SQL query
     *
     * @param $sql
     * @return $this
     */
    public function query($sql) {
        $this->_db->query($sql);
        return $this;
    }

    /**
     * Alias for logs
     *
     * @param $message
     */
    public function log($message) {
        $this->_logger->info($message);
    }

}
