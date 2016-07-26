<?php

class BootstrapCron extends Zend_Application_Bootstrap_Bootstrap
{

    public $_request = null;
    public $_application = null;
    public $_front_controller = false;

    protected function _initPaths() {

        Siberian_Error::init();

        Zend_Loader_Autoloader::getInstance()->registerNamespace('Core');

        $include_paths = array(get_include_path());
        $include_paths[] = realpath(APPLICATION_PATH."/local/modules");
        switch(Siberian_Version::TYPE) {
            case 'PE':
                $include_paths[] = realpath(APPLICATION_PATH."/pe/modules");
            case 'MAE':
                $include_paths[] = realpath(APPLICATION_PATH."/mae/modules");
            case 'SAE':
            default:
                $include_paths[] = realpath(APPLICATION_PATH."/sae/modules");
        }

        /** Updating the include_paths */
        set_include_path(implode(PATH_SEPARATOR, $include_paths));

        $base_path = '';
        if(isset($_SERVER['SCRIPT_FILENAME'])) $base_path = realpath(dirname($_SERVER['SCRIPT_FILENAME']));
        Core_Model_Directory::setBasePath($base_path);

        //for cron we are always at root directory
        $path = '';
        Core_Model_Directory::setPath($path);
    }

    protected function _initLogger() {
        if (!is_dir(Core_Model_Directory::getBasePathTo('var/log'))) {
            mkdir(Core_Model_Directory::getBasePathTo('var/log'), 0777, true);
        }

        $writer = new Zend_Log_Writer_Stream(Core_Model_Directory::getBasePathTo('var/log/cron-output.log'));
        $logger = new Siberian_Log($writer);
        Zend_Registry::set('logger', $logger);
    }

    protected function _initConnection() {

        $this->bootstrap('db');
        $resource = $this->getResource('db');
        Zend_Registry::set('db', $resource);
        if(Installer_Model_Installer::isInstalled()) {
            try {
                $default = new Core_Model_Db_Table();
                $default->checkConnection();
            } catch(Exception $e) {
                $logger = Zend_Registry::get("logger");
                $logger->sendException("Fatal Error When Connecting to The Database: \n".print_r($e, true));
            }
        }
    }

    protected function _initDispatcher() {
        $frontController = Zend_Controller_Front::getInstance();
        $frontController->setDispatcher(new Siberian_Controller_Dispatcher_Standard());
        $this->bootstrap('frontController');
        $this->_front_controller = $frontController;
    }

    /**
     * Core modules were loaded by the app.ini
     *
     * @version 4.1.0
     */
    protected function _initModuleDirectory() {
        $base = Core_Model_Directory::getBasePathTo("app");

        if(!file_exists("$base/local") || !file_exists("$base/local/modules") || !file_exists("$base/local/design")) {
            mkdir("$base/local", 0777);
            mkdir("$base/local/modules", 0777);
            mkdir("$base/local/design", 0777);
        }

        /** Priorities are inverted for controllers */
        switch(Siberian_Version::TYPE) {
            default: case 'SAE':
            $this->_front_controller->addModuleDirectory("$base/sae/modules");
            break;
            case 'MAE':
                $this->_front_controller->addModuleDirectory("$base/sae/modules");
                $this->_front_controller->addModuleDirectory("$base/mae/modules");
                break;
            case 'PE':
                $this->_front_controller->addModuleDirectory("$base/sae/modules");
                $this->_front_controller->addModuleDirectory("$base/mae/modules");
                $this->_front_controller->addModuleDirectory("$base/pe/modules");
                break;
        }

        if(is_readable("$base/local/modules")) {
            $this->_front_controller->addModuleDirectory("$base/local/modules");
        }

        Siberian_Cache_Apps::init();
        Siberian_Utils::load();
    }

    protected function _initCache() {
        $cache_dir = Core_Model_Directory::getCacheDirectory(true);
        if(is_writable($cache_dir)) {
            $frontendConf = array ('lifetime' => 345600, 'automatic_seralization' => true);
            $backendConf = array ('cache_dir' => $cache_dir);
            $cache = Zend_Cache::factory('Core','File',$frontendConf,$backendConf);
            $cache->setOption('automatic_serialization', true);
            Zend_Locale::setCache($cache);
            Zend_Registry::set('cache', $cache);
        }
    }

}
