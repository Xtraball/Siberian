<?php

class Installer_Model_Db_Table_Installer_Module extends Core_Model_Db_Table
{

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
     * @throws Zend_Exception
     */
    public function __construct($options = [])
    {
        $this->_logger = Zend_Registry::get("logger");

        parent::__construct($options);
        try {
            $this->_db->describeTable($this->_name);
        } catch (Exception $e) {
            $this->_is_installed = false;
        }
    }

    /**
     * @return bool
     */
    public function isInstalled()
    {
        return $this->_is_installed;
    }

    /**
     * @param $module
     * @param $file
     * @throws Exception
     */
    public function install($module, $file)
    {

        try {
            $this->query("SET foreign_key_checks = 0;");
            $this->start();
            require_once $file;
            $this->end();
            $this->query("SET foreign_key_checks = 1;");
        } catch (Exception $e1) {
            try {
                $this->_db->rollback();
            } catch (\Exception $e2) {}
            if (APPLICATION_ENV !== 'production') {
                Zend_Debug::dump($e1);
                die;
            }
            throw new \Exception("An error occurred while upgrading the system. Please, contact your administrator.");
        }
    }

    /**
     * Starts a DB Transaction
     */
    public function start()
    {
        try {
            $this->_db->beginTransaction();
        } catch (\Exception $e) {
            //Zend_Debug::dump($e);
        }
    }

    /**
     * Ends a DB Transaction
     */
    public function end()
    {
        try {
            $this->_db->commit();
        } catch (\Exception $e) {
            //Zend_Debug::dump($e);
        }
    }

    /**
     * alias for direct SQL query
     *
     * @param $sql
     * @return $this
     */
    public function query($sql)
    {
        $this->_db->query($sql);
        return $this;
    }

    /**
     * Alias for logs
     *
     * @param $message
     */
    public function log($message)
    {
        $this->_logger->info($message);
    }

}
