<?php

/**
 * Class Bootstrap
 */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    /**
     * @var Siberian_Controller_Request_Http
     */
    public $_request = null;

    /**
     * @var Application_Model_Application
     */
    public $_application = null;

    /**
     * @var Zend_Controller_Front
     */
    public $_front_controller = false;

    /**
     * @throws Zend_Exception
     * @throws Zend_Loader_Exception
     * @throws \DebugBar\DebugBarException
     */
    protected function _initPaths()
    {
        // Clear stat cache to ensure we correctly detect the files
        clearstatcache();

        $loader = \Zend_Loader_Autoloader::getInstance();

        $loader->registerNamespace('Core');
        $loader->registerNamespace('Symfony');
        $loader->registerNamespace('Plesk');
        $loader->registerNamespace('PListEditor');

        $includePaths = [get_include_path()];
        $includePaths[] = realpath(APPLICATION_PATH . '/local/modules');
        switch (\Siberian\Version::TYPE) {
            case 'PE':
                $includePaths[] = realpath(APPLICATION_PATH . '/pe/modules');
                $includePaths[] = realpath(APPLICATION_PATH . '/mae/modules');
                $includePaths[] = realpath(APPLICATION_PATH . '/sae/modules');
                break;
            case 'MAE':
                $includePaths[] = realpath(APPLICATION_PATH . '/mae/modules');
                $includePaths[] = realpath(APPLICATION_PATH . '/sae/modules');
                break;
            case 'SAE':
            default:
                $includePaths[] = realpath(APPLICATION_PATH . '/sae/modules');
        }

        // Updating the include_paths!
        set_include_path(implode_polyfill(PATH_SEPARATOR, $includePaths));

        $base_path = '';
        if (isset($_SERVER['SCRIPT_FILENAME'])) {
            $base_path = realpath(dirname($_SERVER['SCRIPT_FILENAME']));
        }
        \Core_Model_Directory::setBasePath($base_path);

        $this->bootstrap('CacheManager');
        $dbCache = $this->getResource('CacheManager')->getCache('database');
        Zend_Db_Table_Abstract::setDefaultMetadataCache($dbCache);

        // include Stubs
        require_once \Core_Model_Directory::getBasePathTo('/lib/vendor/autoload.php');
        require_once \Core_Model_Directory::getBasePathTo('/lib/Siberian/Pure.php');
        require_once \Core_Model_Directory::getBasePathTo('/lib/Siberian/Stubs.php');

        // Then load class aliases
        \Siberian\Stubs::loadAliases();

        $path = '';
        if (isset($_SERVER['SCRIPT_NAME'])) {
            $path = $_SERVER['SCRIPT_NAME'];
        } else if (isset($_SERVER['PHP_SELF'])) {
            $path = $_SERVER['PHP_SELF'];
        }
        $path = str_replace('/' . basename($path), '', $path);
        \Core_Model_Directory::setPath($path);

        // Init debugger if needed!
        \Siberian\Debug::init();
        \Siberian\Exec::start();
    }

    /**
     *
     */
    protected function _initErrorMessages()
    {
        if (APPLICATION_ENV === 'production') {
            error_reporting(0);
        } else {
            // Reports all errors, handled by Siberian_Error!
            error_reporting(E_ALL);
            Siberian_Error::init();
        }
    }

    /**
     * @throws Zend_Application_Bootstrap_Exception
     * @throws Zend_Cache_Exception
     */
    protected function _initCache()
    {
        $defaultCache = $this->getResource('CacheManager')->getCache('default');
        $outputCache = $this->getResource('CacheManager')->getCache('output');

        $cacheDir = Core_Model_Directory::getCacheDirectory(true);
        if (is_writable($cacheDir)) {
            $frontendConf = [
                'lifetime' => 345600,
                'automatic_seralization' => true
            ];
            $backendConf = [
                'cache_dir' => $cacheDir
            ];
            $cache = Zend_Cache::factory('Core', 'File', $frontendConf, $backendConf);
            $cache->setOption('automatic_serialization', true);
            Zend_Locale::setCache($defaultCache);
            Zend_Registry::set('cache', $defaultCache);
            Zend_Registry::set('cacheOutput', $outputCache);
        }
    }

    /**
     *
     */
    protected function _initHtaccess()
    {
        $oldHtaccess = path('htaccess.txt');
        $newHtaccess = path('.htaccess');
        if (!file_exists($newHtaccess) &&
            is_readable($oldHtaccess) &&
            is_writable(path())) {
            $content = file_get_contents($oldHtaccess);
            $content = str_replace(
                '# ${RewriteBase}',
                'RewriteBase ' . Core_Model_Directory::getPathTo(),
                $content);
            $htaccess = fopen($newHtaccess, "w");
            fputs($htaccess, $content);
            fclose($htaccess);
        }
    }

    /**
     * @throws ReflectionException
     * @throws Zend_Log_Exception
     */
    protected function _initLogger()
    {
        $concurrentDirectory = path('var/log');
        if (!is_dir($concurrentDirectory)) {
            if (!mkdir($concurrentDirectory, 0777, true) &&
                !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }

        $writer = new \Zend_Log_Writer_Stream(path('var/log/output.log'));
        $logger = new \Siberian\Log($writer);
        \Zend_Registry::set('logger', $logger);
    }

    protected function _initConnection()
    {
        $this->bootstrap('db');
        $resource = $this->getResource('db');

        // Set profiler if needed!
        $resource = Siberian_Debug::setProfiler($resource);

        // Disabling strict mode on run!
        try {
            $resource->query('SET sql_mode = \'\';');
        } catch (Exception $e) {
            $logger = Zend_Registry::get('logger');
            $logger->err('Fatal Error when trying to disable SQL strict mode: ' . PHP_EOL . print_r($e, true));
        }

        Zend_Registry::set('db', $resource);
        if (Installer_Model_Installer::isInstalled()) {
            try {
                $default = new Core_Model_Db_Table();
                $default->checkConnection();
            } catch (Exception $e) {
                $logger = Zend_Registry::get('logger');
                $logger->sendException('Fatal Error When Connecting to The Database: ' . PHP_EOL . print_r($e, true));
            }
        }
    }

    /**
     * Permet de garder le nom des modules avec une majuscule et les url en minuscule
     */
    protected function _initDispatcher()
    {
        $frontController = Zend_Controller_Front::getInstance();
        $frontController->setDispatcher(new Siberian_Controller_Dispatcher_Standard());
        $this->bootstrap('frontController');
        $this->_front_controller = $frontController;
    }

    /**
     * Core modules are loaded dynamically depending on the Edition
     *
     * @version 4.12.18
     */
    protected function _initModuleDirectory()
    {
        $base = Core_Model_Directory::getBasePathTo('app');

        if (!is_dir($base . '/local') ||
            !is_dir($base . '/local/modules') ||
            !is_dir($base . '/local/design')) {
            mkdir($base . '/local', 0777);
            mkdir($base . '/local/modules', 0777);
            mkdir($base . '/local/design', 0777);
        }

        // Priorities are inverted for controllers!
        switch (\Siberian\Version::TYPE) {
            default:
            case 'SAE':
                $this->_front_controller->addModuleDirectory($base . '/sae/modules', false);
                break;
            case 'MAE':
                $this->_front_controller->addModuleDirectory($base . '/sae/modules', false);
                $this->_front_controller->addModuleDirectory($base . '/mae/modules', false);
                break;
            case 'PE':
                $this->_front_controller->addModuleDirectory($base . '/sae/modules', false);
                $this->_front_controller->addModuleDirectory($base . '/mae/modules', false);
                $this->_front_controller->addModuleDirectory($base . '/pe/modules', false);
                break;
        }

        if (is_readable($base . '/local/modules')) {
            $this->_front_controller->addModuleDirectory($base . '/local/modules', true);
        }

        Siberian_Utils::load();
    }

    protected function _initInstaller()
    {
        $front = $this->_front_controller;
        $module_names = $front->getDispatcher()->getModuleDirectories();
        Installer_Model_Installer::setModules($module_names);
    }

    protected function _initRequest()
    {
        Core_Model_Language::prepare();
        $frontController = $this->_front_controller;
        $this->_request = new Siberian_Controller_Request_Http();
        $this->_request->isInstalling(!Installer_Model_Installer::isInstalled());
        $this->_request->setPathInfo();
        $baseUrl = $this->_request->getScheme() . '://' . $this->_request->getHttpHost() .
            $this->_request->getBaseUrl();
        $this->_request->setBaseUrl($baseUrl);
        $frontController->setRequest($this->_request);
        Siberian_View::setRequest($this->_request);
        Core_Model_Default::setBaseUrl($this->_request->getBaseUrl());

        // Translator
        Siberian_Cache_Translation::init();
        Core_Model_Translator::init();
    }

    /**
     * Loading individual bootstrappers!
     */
    protected function _initModuleBoostrap()
    {
        $edition_path = strtolower(Siberian_Version::TYPE);
        require_once path("app/{$edition_path}/bootstrap.php");

        Module_Bootstrap::init($this);

        $module_names = $this->_front_controller->getDispatcher()->getModuleDirectories();

        // Mmobilcart is known to cause issues & side-effects
        if (!$this->_request->isInstalling()) {
            __set('mobilcart_warning',
                in_array('Mmobilcart', $module_names, true) ? 'show' : 'dismiss');
        }

        foreach ($module_names as $module) {

            // Skipping disabled module!
            if (!Installer_Model_Installer_Module::sGetIsEnabled($module)) {
                continue;
            }

            $path = $this->_front_controller->getModuleDirectory($module) . '/bootstrap.php';
            $pathInit = $this->_front_controller->getModuleDirectory($module) . '/init.php';

            // Init is the new flavor 5.0, and has priority over bootstrap!
            if (is_readable($pathInit)) {
                try {
                    global $init;
                    ob_start();
                    require_once $pathInit;
                    if (is_callable($init)) {
                        $init($this);
                    }
                    ob_end_clean();
                } catch (Exception $e) {
                    // Silently catch & log malformed init module!
                    trigger_error($e->getMessage());
                }
            } else if (is_readable($path)) {
                try {
                    $classname = $module . '_Bootstrap';

                    // Ensure this Class is not duplicated!
                    if (!class_exists($classname, false)) {
                        require_once $path;
                        if (class_exists($classname)) {
                            $bs = new $classname();
                            if (method_exists($bs, "init")) {
                                $bs::init($this);
                            }
                        }
                    } else {
                        throw new \Siberian\Exception('The bootstrap file located at \'' . $path .
                            '\' redefines/or is already loaded, Class \'' . $classname .
                            '\', please remove it or rename it.');
                    }
                } catch (Exception $e) {
                    // Silently catch & log malformed bootstrap module!
                    trigger_error($e->getMessage());
                }
            }
        }
    }

    /**
     *
     */
    protected function _initDesign()
    {
        if (!$this->_request->isInstalling()) {
            // Monkey patching if needed!
        }

        Siberian_Cache_Design::init();
        $this->getPluginLoader()->addPrefixPath('Siberian_Application_Resource', 'Siberian/Application/Resource');
        Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNeverRender(true);
    }

    protected function _initPurifier()
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', Core_Model_Directory::getBasePathTo('var/cache'));
        $def = $config->getHTMLDefinition(true);

        // Attributes for in-app links
        $def->addAttribute('a', 'data-offline', 'Text');
        $def->addAttribute('a', 'data-params', 'Text');
        $def->addAttribute('a', 'data-state', 'Text');

        $htmlPurifier = new HTMLPurifier($config);

        Zend_Registry::set('htmlPurifier', $htmlPurifier);
    }

    /**
     *
     */
    protected function _initRouter()
    {
        $front = $this->_front_controller;
        $router = $front->getRouter();
        $router
            ->addRoute('default',
                new Siberian_Controller_Router_Route_Module(
                    [],
                    $front->getDispatcher(),
                    $front->getRequest()
                ));
    }

    public function _initMinify()
    {
        // Minify Cache!
        if (Installer_Model_Installer::isInstalled()) {
            $minifier = new Siberian_Minify();
            $minifier->build();
        }
    }

    /**
     * @throws Exception
     */
    protected function _initModules()
    {
        if (!$this->_request->isInstalling()) {
            $front = $this->_front_controller;
            $module_names = $front->getDispatcher()->getSortedModuleDirectories();

            if (APPLICATION_ENV === 'development') {
                foreach ($module_names as $module_name) {
                    $module = new Installer_Model_Installer_Module();
                    $module->prepare($module_name);
                    if ($module->canUpdate()) {
                        $module->install();
                        $module->insertData();
                    }
                }
            }
        }
    }

    /**
     * @throws Zend_Config_Exception
     * @throws Zend_Session_Exception
     */
    public function _initSession()
    {
        $session_ini = Core_Model_Directory::getBasePathTo('/app/configs/session.ini');
        $config = new Zend_Config_Ini($session_ini, 'production');

        $_config = $config->toArray();

        // Awesome session alteration!
        if ($this->_request->isApplication()) {
            $_config['name'] = 'front';
            $_config['use_cookies'] = false;
        }

        Zend_Session::setOptions($_config);
    }

    /**
     * @return mixed|Zend_Controller_Response_Abstract
     * @throws Exception
     * @throws Zend_Application_Bootstrap_Exception
     */
    public function run()
    {
        $front = $this->_front_controller;
        $default = $front->getDefaultModule();

        if (null === $front->getControllerDirectory($default)) {
            throw new Zend_Application_Bootstrap_Exception(
                'No default controller directory registered with front controller'
            );
        }

        $front->setParam('bootstrap', $this);
        $request = $front->getRequest();

        $response = $front->dispatch($request);

        shutdown_extract_p();

        if ($front->returnResponse()) {
            return $response;
        }
    }

}
