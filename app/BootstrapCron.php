<?php

class BootstrapCron extends Zend_Application_Bootstrap_Bootstrap
{

    public $_request = null;
    public $_application = null;
    public $_front_controller = false;

    protected function _initPaths() {

        $loader = Zend_Loader_Autoloader::getInstance();

        $loader->registerNamespace("Core");
        $loader->registerNamespace("Symfony");
        $loader->registerNamespace("Plesk");

        $include_paths = array(get_include_path());
        $include_paths[] = realpath(APPLICATION_PATH."/local/modules");
        switch(Siberian_Version::TYPE) {
            case "PE":
                $include_paths[] = realpath(APPLICATION_PATH."/pe/modules");
            case "MAE":
                $include_paths[] = realpath(APPLICATION_PATH."/mae/modules");
            case "SAE":
            default:
                $include_paths[] = realpath(APPLICATION_PATH."/sae/modules");
        }

        /** Updating the include_paths */
        set_include_path(implode(PATH_SEPARATOR, $include_paths));

        $base_path = "";
        if(isset($_SERVER["SCRIPT_FILENAME"])) {
            $base_path = realpath(dirname($_SERVER["SCRIPT_FILENAME"]));
        } elseif (isset($_SERVER["argv"]) && isset($_SERVER["argv"][0])) {
            $base_path = dirname($_SERVER["argv"]["0"]);
        } else {
            $base_path = substr(dirname(__FILE__),0,-3);
        }
        Core_Model_Directory::setBasePath($base_path);

        //for cron we are always at root directory
        $path = "";
        Core_Model_Directory::setPath($path);

        # External vendor, from composer
        if(version_compare(PHP_VERSION, "5.6.0" , ">=")) {
            $autoloader = Core_Model_Directory::getBasePathTo("/lib/vendor/autoload.php");
            require_once $autoloader;
        }
    }

    protected function _initLogger() {
        if (!is_dir(Core_Model_Directory::getBasePathTo("var/log"))) {
            mkdir(Core_Model_Directory::getBasePathTo("var/log"), 0777, true);
        }

        $writer = new Zend_Log_Writer_Stream(Core_Model_Directory::getBasePathTo("var/log/cron-output.log"));
        $logger = new Siberian_Log($writer);
        Zend_Registry::set("logger", $logger);
    }

    protected function _initConnection() {

        $this->bootstrap("db");
        $resource = $this->getResource("db");

        //Disabling strict mode
        try {
            $resource->query("SET sql_mode = '';");
        } catch(Exception $e) {
            $logger = Zend_Registry::get("logger");
            $logger->sendException("Fatal Error when trying to disable SQL strict mode: \n".print_r($e, true));
        }

        Zend_Registry::set("db", $resource);
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

    /**
     * Permet de garder le nom des modules avec une majuscule et les url en minuscule
     */
    protected function _initDispatcher() {
        $frontController = Zend_Controller_Front::getInstance();
        $frontController->setDispatcher(new Siberian_Controller_Dispatcher_Standard());
        $this->bootstrap("frontController");
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
            default: case "SAE":
            $this->_front_controller->addModuleDirectory("$base/sae/modules");
            break;
            case "MAE":
                $this->_front_controller->addModuleDirectory("$base/sae/modules");
                $this->_front_controller->addModuleDirectory("$base/mae/modules");
                break;
            case "PE":
                $this->_front_controller->addModuleDirectory("$base/sae/modules");
                $this->_front_controller->addModuleDirectory("$base/mae/modules");
                $this->_front_controller->addModuleDirectory("$base/pe/modules");
                break;
        }

        if(is_readable("$base/local/modules")) {
            $this->_front_controller->addModuleDirectory("$base/local/modules");
        }

        Siberian_Utils::load();
    }

    protected function _initLanguages() {
        Core_Model_Language::prepare();
    }

    /** Loading individual bootstrappers */
    protected function _initModuleBoostrap() {

        if(version_compare(PHP_VERSION, "5.6.0" , ">=")) {

            $module_names = $this->_front_controller->getDispatcher()->getModuleDirectories();

            foreach ($module_names as $module) {
                $path = $this->_front_controller->getModuleDirectory($module) . "/bootstrap.php";
                $path_init = $this->_front_controller->getModuleDirectory($module) . "/init.php";

                # Init is the new flavor 5.0, and has priority over bootstrap.
                if (is_readable($path_init)) {

                    try {

                        ob_start();
                        require_once $path_init;
                        if (is_callable($init)) {
                            $init($this);
                        }
                        ob_end_clean();

                    } catch (Exception $e) {
                        # Silently catch & log malformed init module
                        trigger_error($e->getMessage());
                    }

                } else if (is_readable($path)) {

                    try {

                        $classname = "{$module}_Bootstrap";

                        # Ensure this Class is not duplicated.
                        if (!class_exists($classname, false)) {
                            require_once $path;

                            if (class_exists($classname)) {
                                $bs = new $classname();
                                if (method_exists($bs, "init")) {
                                    $bs::init($this);
                                }
                            }

                        } else {
                            throw new Siberian_Exception("The bootstrap file located at '{$path}' redefines/or is already loaded, Class '{$classname}', please remove it or rename it.");
                        }
                    } catch (Exception $e) {
                        # Silently catch & log malformed bootstrap module
                        trigger_error($e->getMessage());
                    }

                }
            }

        }
    }

    protected function _initCache() {
        if(version_compare(PHP_VERSION, "5.6.0" , ">=")) {

            Siberian_Cache_Design::init();

            $this->bootstrap("CacheManager");
            $default_cache = $this->getResource("CacheManager")->getCache("default");

            $cache_dir = Core_Model_Directory::getCacheDirectory(true);
            if (is_writable($cache_dir)) {
                $frontendConf = array("lifetime" => 345600, "automatic_seralization" => true);
                $backendConf = array("cache_dir" => $cache_dir);
                $cache = Zend_Cache::factory("Core", "File", $frontendConf, $backendConf);
                $cache->setOption("automatic_serialization", true);
                Zend_Locale::setCache($default_cache);
                Zend_Registry::set("cache", $default_cache);
            }
        }
    }

}
