<?php

/**
 * Class BootstrapCron
 */
class BootstrapCron extends Zend_Application_Bootstrap_Bootstrap
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
     * @throws Zend_Loader_Exception
     */
    protected function _initPaths()
    {
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

        $this->bootstrap('CacheManager');
        $dbCache = $this->getResource('CacheManager')->getCache('database');
        Zend_Db_Table_Abstract::setDefaultMetadataCache($dbCache);

        if (isset($_SERVER['SCRIPT_FILENAME'])) {
            $base_path = realpath(dirname($_SERVER['SCRIPT_FILENAME']));
        } elseif (isset($_SERVER['argv']) && isset($_SERVER['argv'][0])) {
            $base_path = dirname($_SERVER['argv']['0']);
        } else {
            $base_path = substr(dirname(__FILE__), 0, -3);
        }
        \Core_Model_Directory::setBasePath($base_path);

        // include Stubs
        require_once \Core_Model_Directory::getBasePathTo('/lib/vendor/autoload.php');
        require_once \Core_Model_Directory::getBasePathTo('/lib/Siberian/Pure.php');
        require_once \Core_Model_Directory::getBasePathTo('/lib/Siberian/Stubs.php');

        // Then load class aliases
        \Siberian\Stubs::loadAliases();

        //for cron we are always at root directory
        \Core_Model_Directory::setPath('');
    }

    protected function _initLogger()
    {
        if (!is_dir(Core_Model_Directory::getBasePathTo('var/log'))) {
            mkdir(Core_Model_Directory::getBasePathTo('var/log'), 0777, true);
        }

        $writer = new Zend_Log_Writer_Stream(Core_Model_Directory::getBasePathTo('var/log/output.log'));
        $logger = new Siberian_Log($writer);
        Zend_Registry::set('logger', $logger);
    }

    protected function _initCache()
    {
        Siberian_Cache_Design::init();
        $this->bootstrap('CacheManager');
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

    protected function _initConnection()
    {
        $this->bootstrap('db');
        $resource = $this->getResource('db');

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

    protected function _initLanguages()
    {
        Core_Model_Language::prepare();

        // Translator
        Siberian_Cache_Translation::init();
        Core_Model_Translator::init();
    }

    /**
     * Loading individual bootstrappers!
     */
    protected function _initModuleBoostrap()
    {
        $module_names = $this->_front_controller->getDispatcher()->getModuleDirectories();

        // Hotcheck ACL Admin!
        $rootRole = (new Acl_Model_Role())->find(1);
        if ($rootRole->getParentId()) {
            $rootRole->setParentId(null)->save();

            $logger = Zend_Registry::get('logger');
            $logger->info('Admin parent_id was reset to null.');
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
}
