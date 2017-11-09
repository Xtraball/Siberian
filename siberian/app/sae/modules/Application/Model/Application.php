<?php

class Application_Model_Application extends Application_Model_Application_Abstract {

    protected static $_instance;

    /**
     * @return Application_Model_Application
     */
    public static function getInstance() {
        if(!self::$_instance) {
            self::$_instance = new self();
            self::$_instance->find(1);
        }
        return self::$_instance;
    }

    public function getKey() {
        return self::OVERVIEW_PATH;
    }

    public function getApplication() {
        return self::getInstance();
    }

}
