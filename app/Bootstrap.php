<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
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

        $path = '';
        if(isset($_SERVER['SCRIPT_NAME'])) $path = $_SERVER['SCRIPT_NAME'];
        else if(isset($_SERVER['PHP_SELF'])) $path = $_SERVER['PHP_SELF'];
        $path = str_replace('/'.basename($path), '', $path);
        Core_Model_Directory::setPath($path);
    }

    protected function _initErrorMessages() {

        if(APPLICATION_ENV == "production") {
            error_reporting(0);
        } else {
            error_reporting(32767);
        }
    }

    protected function _initSecurityPatches() {
        # TG-171 app.ini.bck
        $path = Core_Model_Directory::getBasePathTo("var/tmp/app.ini.bck");
        if(file_exists($path)) {
            unlink($path);
        }

        # Backup removal
        $path = Core_Model_Directory::getBasePathTo("");
        $files = glob("{$path}/backup-*.zip");
        foreach($files as $file) {
            unlink($file);
        }
    }

    protected function _initHtaccess() {

        $old_htaccess = Core_Model_Directory::getBasePathTo('htaccess.txt');
        $new_htaccess = Core_Model_Directory::getBasePathTo('.htaccess');
        if(!file_exists($new_htaccess) AND is_readable($old_htaccess) AND is_writable(Core_Model_Directory::getBasePathTo())) {
            $content = file_get_contents($old_htaccess);
            $content = str_replace('# ${RewriteBase}', 'RewriteBase '.Core_Model_Directory::getPathTo(), $content);
            $htaccess = fopen($new_htaccess, 'w');
            fputs($htaccess, $content);
            fclose($htaccess);
        }

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

    /**
     * Permet de garder le nom des modules avec une majuscule et les url en minuscule
     */
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

        Siberian_Design::init();
    }

    protected function _initLogger() {
        if (!is_dir(Core_Model_Directory::getBasePathTo('var/log'))) {
            mkdir(Core_Model_Directory::getBasePathTo('var/log'), 0777, true);
        }

        $writer = new Zend_Log_Writer_Stream(Core_Model_Directory::getBasePathTo('var/log/output.log'));
        $logger = new Siberian_Log($writer);
        Zend_Registry::set('logger', $logger);
    }

    protected function _initInstaller() {
        $front = $this->_front_controller;
        $module_names = $front->getDispatcher()->getModuleDirectories();
        Installer_Model_Installer::setModules($module_names);
    }

    protected function _initRequest() {

        Core_Model_Language::prepare();
        $frontController = $this->_front_controller;
        $this->_request = new Siberian_Controller_Request_Http();
//        $this->_request->setBackofficeUrl($this->getOption('backofficeUrl'));
        $this->_request->isInstalling(!Installer_Model_Installer::isInstalled());
        $this->_request->setPathInfo();
        $baseUrl = $this->_request->getScheme().'://'.$this->_request->getHttpHost().$this->_request->getBaseUrl();
        $this->_request->setBaseUrl($baseUrl);
        $frontController->setRequest($this->_request);
        Siberian_View::setRequest($this->_request);
        Core_Model_Default::setBaseUrl($this->_request->getBaseUrl());
    }

    /**  Loading individual bootstrappers */
    protected function _initModuleBoostrap() {
        /** This is a part of a transition update @remove >4.1.0 */
        if(version_compare(Siberian_Version::VERSION, "4.1.0", ">=")) {
            $edition_path = strtolower(Siberian_Version::TYPE);
            require_once Core_Model_Directory::getBasePathTo("app/{$edition_path}/bootstrap.php");

            Module_Bootstrap::init($this);
        }
    }

    protected function _initDesign() {

        $this->getPluginLoader()->addPrefixPath('Siberian_Application_Resource', 'Siberian/Application/Resource');

        Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNeverRender(true);
    }

    protected function _initRouter() {

        $front = $this->_front_controller;
        $router = $front->getRouter();
        $router->addRoute('default', new Siberian_Controller_Router_Route_Module(array(), $front->getDispatcher(), $front->getRequest()));
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

    protected function _initModules() {


        if(!$this->_request->isInstalling()) {

            $front = $this->_front_controller;
            $module_names = $front->getDispatcher()->getSortedModuleDirectories();

            if(APPLICATION_ENV == "development") {

                foreach($module_names as $module_name) {
                    $module = new Installer_Model_Installer_Module();
                    $module->prepare($module_name);
                    if($module->canUpdate()) {
                        $module->install();
                        $module->insertData();
                    }
                }

            }
        }
    }

    public function run() {

        $front   = $this->_front_controller;
        $default = $front->getDefaultModule();

        if (null === $front->getControllerDirectory($default)) {
            throw new Zend_Application_Bootstrap_Exception(
                'No default controller directory registered with front controller'
            );
        }

        $front->setParam('bootstrap', $this);
        $request = $front->getRequest();

        $response = $front->dispatch($request);
        if ($front->returnResponse()) {
            return $response;
        }

        Siberian_Error::end();
    }

}
