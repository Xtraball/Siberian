<?php

/**
 * Class Application_Model_Application
 */
class Application_Model_Application extends Application_Model_Application_Abstract
{
    /**
     * @var Application_Model_Application
     */
    protected static $_instance;

    /**
     * @return Application_Model_Application
     * @throws Zend_Exception
     */
    public static function getInstance()
    {
        if (!self::$_instance) {
            self::$_instance = new self();
            self::$_instance->find(1);
        }
        return self::$_instance;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return self::OVERVIEW_PATH;
    }

    /**
     * @return Application_Model_Application
     * @throws Zend_Exception
     */
    public function getApplication()
    {
        return self::getInstance();
    }

}
