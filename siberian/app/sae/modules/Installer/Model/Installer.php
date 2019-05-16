<?php

/**
 * Class Installer_Model_Installer
 *
 * @method $this setModuleName(string $moduleName)
 */
class Installer_Model_Installer extends Core_Model_Default
{
    /**
     * @var
     */
    public $_parser;

    /**
     * @var array
     */
    protected static $_modules = [];

    /**
     * @var array
     */
    public static $_functions = [
        'exec',
    ];

    /**
     * @var array
     */
    public static $_extensions = [
        'SimpleXML',
        'pdo_mysql',
        'gd',
        'mbstring',
        'iconv',
        'curl',
        'openssl',
    ];

    /**
     * @var array
     */
    public static $_binaries = [
        'zip',
        'unzip',
    ];

    /**
     * @var array
     */
    public static $_errors = [];

    /**
     * Installer_Model_Installer constructor.
     * @param array $params
     * @throws Zend_Exception
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        return $this;
    }

    /**
     * @return bool
     */
    public static function hasRequiredPhpVersion()
    {
        $supOrEqual56 = version_compare(PHP_VERSION, '5.6', '>=');

        return $supOrEqual56;
    }

    /**
     * @return bool
     */
    public static function isInstalled()
    {
        try {
            if (!is_file(APPLICATION_PATH . '/configs/app.ini')) {
                throw new Exception('');
            }
            $ini = new Zend_Config_Ini(APPLICATION_PATH . '/configs/app.ini', APPLICATION_ENV);
            $isInstalled = (boolean)$ini->isInstalled;
        } catch (Exception $e) {
            $isInstalled = false;
        }

        return $isInstalled;
    }

    /**
     * @return array
     */
    public static function checkPermissions()
    {
        $errors = [];
        $base_path = Core_Model_Directory::getBasePathTo('/');
        if (is_file($base_path . 'htaccess.txt') AND !file_exists($base_path . ".htaccess")) {
            if (!is_writable($base_path)) {
                $errors[] = 'The root directory /';
            }
            if (!is_writable($base_path . 'htaccess.txt')) {
                $errors[] = '/htaccess.txt';
            }
        }

        //check directories
        $paths = ['var', 'var/cache', 'var/session', 'var/tmp'];
        foreach ($paths as $path) {
            if (!is_dir($base_path . $path)) {
                mkdir($base_path . $path, 0777);
            }
            if (!is_writable($base_path . $path)) {
                $errors[] = $path;
            }
        }

        //check files

        $paths = [
            "app/configs",
        ];

        foreach ($paths as $path) {
            if (!is_writable($base_path . $path)) {
                $errors[] = $path;
            }
        }

        return $errors;
    }

    /**
     * @param $modules
     */
    public static function setModules($modules)
    {
        self::$_modules = array_map('strtolower', $modules);
    }

    /**
     * @return array
     */
    public static function getModules()
    {
        return self::$_modules;
    }

    /**
     * @param $name
     * @return bool
     */
    public static function hasModule($name)
    {
        return in_array(strtolower($name), self::$_modules);
    }

    /**
     * @param $file
     * @return $this
     * @throws Exception
     */
    public function parse($file)
    {
        $this->_parser = new Installer_Model_Installer_Module_Parser();
        $this->_parser->setFile($file)
            ->extract();

        $this->_parser->checkDependencies();

        return $this;

    }

    /**
     * @return mixed
     */
    public function getPackageDetails()
    {
        return $this->_parser->getPackageDetails();
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function install()
    {
        (new Installer_Model_Installer_Module())
            ->prepare($this->getModuleName())
            ->install();

        return $this;
    }

    /**
     * @return $this
     */
    public function insertData()
    {
        (new Installer_Model_Installer_Module())
            ->prepare($this->getModuleName())
            ->insertData();

        return $this;
    }

    /**
     * @return bool
     */
    public static function setIsInstalled()
    {
        try {
            if (!self::isInstalled()) {
                $writer = new Zend_Config_Writer_Ini();
                $config = new Zend_Config_Ini(
                    APPLICATION_PATH . '/configs/app.ini',
                    null,
                    [
                        'skipExtends' => true,
                        'allowModifications' => true
                    ]);
                $config->production->isInstalled = "1";
                $writer
                    ->setConfig($config)
                    ->setFilename(APPLICATION_PATH . '/configs/app.ini')
                    ->write();

                // Fixing extended bootstrap path!
                $appIni = file_get_contents(APPLICATION_PATH . '/configs/app.ini');
                $replacedIni = preg_replace(
                    '/bootstrap.path = .*/',
                    'bootstrap.path = APPLICATION_PATH "/Bootstrap.php"',
                    $appIni);
                file_put_contents(APPLICATION_PATH . '/configs/app.ini', $replacedIni);
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     *
     */
    public static function testPhp()
    {
        if (!self::hasRequiredPhpVersion()) {
            self::$_errors[] =
                __("Your PHP version %s is not supported, PHP versions from 5.6 to 7.0 are supported.",
                    PHP_VERSION);
        }
    }

    /**
     *
     */
    public static function testFunctions()
    {
        foreach (self::$_functions as $function) {
            if (!function_exists($function)) {
                self::$_errors[] = 'Please enable/add function: ' . $function . '()';
            }
        }
    }

    /**
     *
     */
    public static function testExtensions()
    {
        foreach (self::$_extensions as $extension) {
            if (!extension_loaded($extension)) {
                self::$_errors[] = 'Please enable/add extension: ' . $extension;
            }
        }
    }

    /**
     *
     */
    //public static function testExec()
    //{
    //    if (function_exists('exec')) {
    //        $which1 = exec('which zip');
    //        if (empty($which1)) {
    //            //self::$_errors[] = 'Please enable/add binary: zip';
    //        }
//
    //        $which2 = exec('which unzip');
    //        if (empty($which2)) {
    //            //self::$_errors[] = 'Please enable/add binary: unzip';
    //        }
//
    //    } else {
    //        self::$_errors[] = 'Please enable/add function: exec()';
    //    }
    //}

    /**
     *
     */
    public static function testOpenSSL()
    {
        if (OPENSSL_VERSION_NUMBER < 268439647) {
            self::$_errors[] = 'Please update OpenSSL to 1.0.1+';
        }
    }

    /**
     *
     */
    public static function testPermissions()
    {
        $basePath = Core_Model_Directory::getBasePathTo('/');
        if (is_file($basePath . 'htaccess.txt') &&
            !is_file($basePath . ".htaccess")) {
            if (!is_writable($basePath)) {
                self::$_errors[] = '/ is not writeable';
            }
            if (!is_writable($basePath . 'htaccess.txt')) {
                self::$_errors[] = 'htaccess.txt is not writeable';
            }
        }

        //check directories
        $paths = ['var', 'var/cache', 'var/session', 'var/tmp'];
        foreach ($paths as $path) {
            if (!is_dir($basePath . $path)) {
                mkdir($basePath . $path, 0777);
            }
            if (!is_writable($basePath . $path)) {
                self::$_errors[] = $path . ' is not writeable';
            }
        }

        //check files
        $paths = [
            'app/configs',
        ];

        foreach ($paths as $path) {
            if (!is_writable($basePath . $path)) {
                self::$_errors[] = $path . ' is not writeable';
            }
        }
    }

    /**
     *
     */
    public static function runTest()
    {
        self::testPhp();
        self::testFunctions();
        self::testExtensions();
        //self::testExec();
        self::testOpenSSL();
        self::testPermissions();
    }

}
