<?php

namespace Helper;

use Codeception\Module;

class ZF1Helper extends Module
{
    public static $basePath = '/home/anders/devs/xtraball/Siberian/siberian';

    public function _initialize()
    {
        require_once self::$basePath . '/lib/vendor/autoload.php';
        require_once self::$basePath . '/lib/System/polyfills.php';
        require_once self::$basePath . '/lib/System/defines.php';

        set_include_path(implode_polyfill(PATH_SEPARATOR, [
//            self::$basePath . '/',
            self::$basePath . '/lib',
//            self::$basePath . '/lib/vendor',
            self::$basePath . '/lib/Siberian',
            self::$basePath . '/lib/Zend',
            self::$basePath . '/app/local/modules',
            self::$basePath . '/app/sae/modules',
        ]));

        require_once self::$basePath . '/lib/Zend/Application.php';
        require_once self::$basePath . '/lib/Zend/Loader/Autoloader.php';
        require_once self::$basePath . '/lib/Siberian/Pure.php';

        // Initialize Zend Framework 1 Autoloader
        $autoloader = \Zend_Loader_Autoloader::getInstance();
        $autoloader->setFallbackAutoloader(true);

        // Optionally, add specific namespace paths or resource loading
        $autoloader->registerNamespace('Zend');
        $autoloader->registerNamespace('Core');
        $autoloader->registerNamespace('Admin');
        // You can add other initialization code here, if needed

        \Core_Model_Directory::setBasePath(self::$basePath);

        // Logger
        $writer = new \Zend_Log_Writer_Stream(path('var/log/output.log'));
        $logger = new \Siberian\Log($writer);
        \Zend_Registry::set('logger', $logger);

        // Locale
        \Zend_Registry::set('Zend_Locale', new \Zend_Locale('en_US'));

        // Set test database instance
        $testDbConfig = [
            'host' => '127.0.0.1',
            'username' => 'root',
            'password' => 'toor',
            'dbname' => 'siberian_test',
            'adapterNamespace' => 'Siberian_Db_Adapter',
            'charset' => 'UTF8'
        ];

        $testDb = \Zend_Db::factory('Pdo_Mysql', $testDbConfig);
        \Zend_Registry::set('db', $testDb);

        // Set default mysql mode
        $testDb->query("SET sql_mode = '';");

        \Siberian\Stubs::loadAliases();

        // Translations
        \Siberian\Cache\Translation::init();
        \Core_Model_Translator::init();

        // Autoload
        require self::$basePath . '/lib/vendor/autoload.php';
    }
}