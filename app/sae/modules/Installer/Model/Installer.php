<?php

class Installer_Model_Installer extends Core_Model_Default {

    protected static $_modules = array();

    public function __construct($params = array()) {
        parent::__construct($params);
        return $this;
    }

    public static function hasRequiredPhpVersion() {
        return version_compare(PHP_VERSION, '5.6.0') >= 0;
    }

    public static function isInstalled() {

        $isInstalled = false;
        try {
            if(!file_exists(APPLICATION_PATH . '/configs/app.ini')) {
                throw new Exception('');
            }
            $ini = new Zend_Config_Ini(APPLICATION_PATH . '/configs/app.ini', APPLICATION_ENV);
            $isInstalled = (bool) $ini->isInstalled;

        } catch (Exception $e) {
            $isInstalled = false;
        }

        return $isInstalled;
    }

    public static function checkRequiredPhpVersion() {
        $error = array();

        if(!self::hasRequiredPhpVersion()) {
            $error[] = Core_Model_Translator::translate("Your PHP version is too old, please upgrade to PHP 5.6+.");
        }

        return $error;
    }

    public static function checkExtensions() {

        $errors = array();

        try {

            $mbString = false;
            if(function_exists("mb_strtolower")) {
                $mbString = mb_strtolower("MB_STRING", "UTF-8");
            }

            if(!$mbString) {
                $errors[] = "mbstring";
            }

        } catch(Exception $e) {
            $errors[] = "mbstring";
        }

        try {
            $command = 'zip -L';
            $output = array();
            $code = 0;
            if (function_exists('exec')) {
                exec($command, $output, $code);
                // 127 code for missing command 0 for success
                if ($code !== 0)
                    throw new Exception('zip:command not found');
            } else {
                throw new Exception('exec php function is disabled');
            }
        } catch (Exception $e) {
            $errors[] = "zip shell command";
        }

        try {

            $img = false;
            if(function_exists("imagecreatetruecolor")) {
                $img = imagecreatetruecolor(10, 10);
            }

            if (!$img) {
                $errors[] = "gd2";
            }

        } catch(Exception $e) {
            $errors[] = "gd2";
        }

        try {

            if(!function_exists("iconv")) {
                throw new Exception('iconv module is missing');
            }

        } catch(Exception $e) {
            $errors[] = "iconv";
        }

        try {

            $body = false;
            if(function_exists("curl_init")) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "http://www.google.com");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                $body = curl_exec($ch);
                curl_close($ch);
            }

            if(empty($body)) {
                $errors[] = "cURL";
            }

        } catch(Exception $e) {
            $errors[] = "cURL";
        }

        return $errors;

    }

    public static function checkPermissions() {

        $errors = array();
        $base_path = Core_Model_Directory::getBasePathTo('/');
        if(is_file($base_path.'htaccess.txt') AND !file_exists($base_path.".htaccess")) {
            if(!is_writable($base_path)) {
                $errors[] = 'The root directory /';
            }
            if(!is_writable($base_path.'htaccess.txt')) {
                $errors[] = '/htaccess.txt';
            }
        }

        //check directories
        $paths = array('var', 'var/cache', 'var/session', 'var/tmp');
        foreach($paths as $path) {
            if(!is_dir($base_path.$path)) {
                mkdir($base_path.$path, 0777);
            }
            if(!is_writable($base_path.$path)) {
                $errors[] = $path;
            }
        }

         //check files
        $androidConfigXMLPath = implode(DIRECTORY_SEPARATOR, array(
            Application_Model_Device_Ionic_Android::SOURCE_FOLDER,
            "res",
            "xml",
            "config.xml"
        ));

        $iosConfigXMLPath = implode(DIRECTORY_SEPARATOR, array(
                Application_Model_Device_Ionic_Ios::SOURCE_FOLDER,
                "AppsMobileCompany",
                "config.xml"
        ));

        $iosNoAdsConfigXMLPath = implode(DIRECTORY_SEPARATOR, array(
                Application_Model_Device_Ionic_Ios::SOURCE_FOLDER."-noads",
                "AppsMobileCompany",
                "config.xml"
        ));

        $paths = array(
            "app/configs",
            $androidConfigXMLPath,
            $iosConfigXMLPath,
            $iosNoAdsConfigXMLPath
        );

        foreach($paths as $path) {
            if(!is_writable($base_path.$path)) {
                $errors[] = $path;
            }
        }

        return $errors;
    }

    public static function setModules($modules) {
        self::$_modules = array_map("strtolower", $modules);
    }

    public static function getModules() {
        return self::$_modules;
    }

    public static function hasModule($name) {
        return in_array(strtolower($name), self::$_modules);
    }

    public function parse($file) {

        $this->_parser = new Installer_Model_Installer_Module_Parser();
        $this->_parser->setFile($file)
            ->extract()
        ;

        $this->_parser->checkDependencies();

        return $this;

    }

    public function getPackageDetails() {
        return $this->_parser->getPackageDetails();
    }

    public function install() {
        $module = new Installer_Model_Installer_Module();
        $module->prepare($this->getModuleName())
            ->install()
        ;
        return $this;
    }

    public function insertData() {
        $module = new Installer_Model_Installer_Module();
        $module->prepare($this->getModuleName())
            ->insertData()
        ;
        return $this;
    }

    public static function setIsInstalled() {

        try {

            if(!self::isInstalled()) {
                $writer = new Zend_Config_Writer_Ini();

                $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/app.ini', null, array('skipExtends' => true, 'allowModifications' => true));
                $config->production->isInstalled = "1";

                $writer->setConfig($config)
                    ->setFilename(APPLICATION_PATH . '/configs/app.ini')
                    ->write()
                ;
            }

            return true;

        } catch (Exception $e) {
            return false;
        }

    }

}
