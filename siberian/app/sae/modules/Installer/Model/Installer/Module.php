<?php

use Siberian\File;

/**
 * Class Installer_Model_Installer_Module
 */
class Installer_Model_Installer_Module extends Core_Model_Default
{
    const DEFAULT_VERSION = '0.0.1';

    /**
     * @var string
     */
    public $_lastVersion;

    /**
     * @var string
     */
    protected $_name;

    /**
     * @var string|null
     */
    protected $_code = null;

    /**
     * @var bool
     */
    protected $_useLicense = false;

    /**
     * @var array
     */
    protected $_dbFiles = [];

    /**
     * @var array
     */
    protected $_schemaFiles = [];

    /**
     * @var bool
     */
    protected $_isInstalled = false;

    /**
     * @var null
     */
    protected $_packageInfo = null;

    /**
     * @var
     */
    protected $_basePath;

    /**
     * @var string
     */
    protected $_db_table = Installer_Model_Db_Table_Installer_Module::class;

    /**
     * @var null
     */
    protected $_features = null;

    /**
     * @param $name
     * @param bool $fetch
     * @return $this
     * @throws Zend_Db_Profiler_Exception
     * @throws Zend_Db_Statement_Exception
     */
    public function prepare($name, $fetch = true)
    {
        $this->_name = $name;
        $this->findByName($name);

        if (!$this->getId()) {
            $this->setName($name)
                ->setVersion(self::DEFAULT_VERSION);
            $this->_isInstalled = false;
        } else {
            $this->_isInstalled = true;
        }

        if ($fetch) {
            $this->fetchModule($name);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function reset(): self
    {
        $this->_lastVersion = null;
        $this->_dbFiles = [];
        $this->_isInstalled = false;
        $this->_basePath = null;
        $this->_code = null;
        $this->_useLicense = false;
        return $this;
    }

    /**
     * @param $isEnabled
     * @return $this
     * @throws Zend_Exception
     */
    public function toggleIsEnabled($isEnabled): self
    {
        // Finding module path
        $moduleLockPath = path('/app/local/modules/' . $this->getData('name') . '/module.disabled');
        if ($isEnabled && is_file($moduleLockPath)) {
            unlink($moduleLockPath);
        } else {
            File::putContents($moduleLockPath, 1);
        }

        return $this;
    }

    /**
     * @param $module
     * @return bool
     */
    public static function sGetIsEnabled($module): bool
    {
        $moduleLockPath = path('/app/local/modules/' . $module . '/module.disabled');

        return (!is_readable($moduleLockPath));
    }

    /**
     * @param $name
     * @return $this
     */
    public function findByName($name)
    {

        if ($this->getTable()->isInstalled()) {
            $this->find($name, 'Name');
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Whether the module is installed or not
     *
     * @return bool
     */
    public function isInstalled()
    {
        return $this->_isInstalled;
    }

    /**
     * @return mixed
     */
    public function canUpdate()
    {
        return version_compare($this->_lastVersion, $this->getVersion(), '>');
    }

    /**
     * @throws Exception
     * @throws Zend_Exception
     */
    public function install()
    {
        if ($this->canUpdate()) {

            # Syncing DB only if needed
            $migration_tables = [];
            foreach ($this->_schemaFiles as $table_name => $filename) {
                $migration_table = new Siberian_Migration_Db_Table($table_name);
                $migration_table->setSchemaPath($filename);

                # Test if table exist, if yes try to update, otherwise, try to create.
                $migration_table->tableExists();

                $migration_tables[] = $migration_table;
            }

            # Dependencies injector (mainly for installation purpose)
            if (isset($this->_packageInfo["dependencies"]["modules"])) {
                foreach ($this->_packageInfo["dependencies"]["modules"] as $module => $version) {
                    $depModule = new Installer_Model_Installer_Module();
                    $depModule->prepare($module);

                    if (!$depModule->isInstalled()) {
                        $depModule->install();
                    }

                    if (!version_compare($depModule->_lastVersion, $version, ">=")) {
                        throw new Exception("The installed module {$module}@{$depModule->getVersion()} does not satisfy required {$module}@{$version}, aborting.");
                    }
                }
            }

            if (isset($this->_packageInfo['type'])) {
                $type = $this->_packageInfo['type'];
                if (in_array($type, ['icons', 'layout', 'template'])) {
                    $this
                        ->setType($type)
                        ->save();
                }
            }

            # Now update the foreign keys.
            foreach ($migration_tables as $table) {
                $table->updateForeignKeys();
            }

            // Save code/license things
            $this
                ->setCode($this->_code)
                ->setUseLicense($this->_useLicense)
                ->save();
        }
    }

    /**
     *
     */
    public function insertData()
    {

        $this->save();

        # Processing data files
        foreach ($this->_dbFiles as $index => $file) {
            // Skip old numeric style format files, and *.ec.php wich are encrypted!
            if (preg_match("/.*([0-9\.]+)\.php$/", $file) ||
                preg_match("/\.ec\.php$/", $file)) {
                continue;
            }

            // *.install.php is a backward compat for old modules!
            if (preg_match("/.*install\.php$/", $file)) {
                if (!$this->isInstalled()) {
                    $this->_run($file);
                }
            } else {
                $this->_run($file);
            }

        }

        $this->save();

        # Set the version to the last in package.json
        if (version_compare($this->_lastVersion, $this->getVersion(), '>')) {
            $this->setVersion($this->_lastVersion)->save();
        }
    }

    /**
     * @param $path
     * @return bool|mixed
     */
    protected function readPackage($path)
    {
        // Support for "pre-7.3" versions, just to be sure!
        defined('JSON_THROW_ON_ERROR') || define('JSON_THROW_ON_ERROR', 4194304);

        $content = file_get_contents($path);
        $result = false;
        if (!empty($content)) {
            try {
                if (version_compare(PHP_VERSION, '7.3.0') < 0) {
                    $result = json_decode($content, true, 512);
                    if ($result === null) {
                        throw new \Siberian\Exception(__('Invalid JSON file %s', $path));
                    }
                } else {
                    $result = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
                }
            } catch (\Exception $e) {
                $result = false;
                log_err($e->getMessage());
            }
        }

        return $result;
    }

    /**
     *
     */
    public function fetch()
    {
        if (is_array($this->_data)) {
            $name = $this->_data['name'];
            if (!empty($name)) {
                $this->fetchModule($name);
            }
        }
    }

    /**
     * @param $module_name
     * @throws Zend_Db_Profiler_Exception
     * @throws Zend_Db_Statement_Exception
     */
    protected function fetchModule($module_name)
    {
        $basePath = path('app/sae/modules/' . $module_name);
        $editions = Siberian_Cache_Design::$editions[strtolower(Siberian_Version::TYPE)];

        /** fetching package.json */
        $package_info = false;
        $package_files = [];
        foreach ($editions as $edition) {
            $folder = str_replace("/sae/", "/$edition/", $basePath);
            if (is_readable($folder . "/package.json")) {
                $package_files[] = $folder . "/package.json";
                # Don't break in case another package.json exists
            }
        }

        //we get higher package version
        $highest_package_version = "0.0.0";
        foreach ($package_files as $package_file) {
            $current_package_info = $this->readPackage($package_file);
            if ($current_package_info === false) {
                continue;
            }

            $current_package_info_version = "0.0.0";
            if (array_key_exists("version", $current_package_info) && !empty($current_package_info["version"])) {
                $current_package_info_version = $current_package_info["version"];
            }

            if (version_compare($current_package_info_version, $highest_package_version, '>')) {
                $package_info = $current_package_info;
                $highest_package_version = $current_package_info_version;
                $this->_basePath = dirname($package_file);
                $this->_code = $current_package_info["code"] ?? null;
                $this->_useLicense = !is_null($this->_code);
            }
        }

        if (!$package_info) {
            if (APPLICATION_ENV === 'development') {
                trigger_error(
                    "Error: 'package.json' is missing from {$module_name}, unable to install/update the Module.",
                    E_USER_WARNING);

                if (!file_exists(Core_Model_Directory::getBasePathTo('app/local/modules/' . $module_name))) {
                    // Remove entry from DB
                    $db = Zend_Db_Table::getDefaultAdapter();
                    $db->query('DELETE FROM module WHERE name = "' . $module_name . '";');
                }
            }
            return;
        }

        $this->_packageInfo = $package_info;

        /** Get the schema for installation/sync */
        foreach ($editions as $edition) {
            $folder = str_replace("/sae/", "/$edition/", $basePath);
            if (is_readable($folder . "/resources/db/schema")) {
                $files = new DirectoryIterator($folder . "/resources/db/schema");
                foreach ($files as $file) {
                    if (!$file->isDot()) {
                        $table_name = str_replace(".php", "", basename($file->getFilename()));
                        # Higher schema should override, and skip *.ec.php
                        if (!preg_match("/\.ec\.php$/", $file->getFilename())) {
                            $this->_schemaFiles[$table_name] = $folder . "/resources/db/schema/" . $file->getFilename();
                        }
                    }
                }
            }
        }

        foreach ($editions as $edition) {
            $folder = str_replace("/sae/", "/$edition/", $basePath);
            if (is_readable($folder . "/resources/db/data")) {
                $files = new DirectoryIterator($folder . "/resources/db/data");
                foreach ($files as $file) {
                    /** Database & Template updates */
                    if (preg_match("/^(.*)\.php$/", $file->getFilename())) {
                        $this->_dbFiles[] = $file->getPathName();
                    }
                }
            }
        }

        $this->_lastVersion = $package_info["version"];
    }

    /**
     * @param $file
     * @return $this
     * @throws Zend_Exception
     */
    public function _run($file)
    {

        try {
            $this->getTable()->install($this->getName(), $file);
        } catch (Exception $e) {
            $logger = Zend_Registry::get("logger");
            $logger->sendException("Fatal Error When Connecting to The Database: \n" . print_r($e, true));
        }

        return $this;
    }

    /**
     * @param $file
     * @return $this
     * @throws Zend_Exception
     */
    protected function _installTemplate($file)
    {
        try {
            $this->getTable()->install("", $file, "");
        } catch (Exception $e) {
            unlink($file);
            $message = "
                Fatal Error When installing the Template (" . $file . ")
                File removed.
                " . print_r($e, true) . "";

            $logger = Zend_Registry::get("logger");
            $logger->sendException($message);
        }
        return $this;
    }

    /**
     * Loads translations contained in the module
     */
    public function loadTranslations()
    {
        $module_folder = new DirectoryIterator($this->_basePath);
        $translation_modules = [];

        if ($module_folder->isDir() && is_readable("{$module_folder->getPathname()}/resources/translations/")) {

            $modules_translations = new DirectoryIterator("{$module_folder->getPathname()}/resources/translations/");

            foreach ($modules_translations as $modules_translation) {

                if ($modules_translation->isDir() && !$modules_translation->isDot()) {
                    /** Looping trough files */
                    $files = new DirectoryIterator($modules_translation->getPathname());
                    foreach ($files as $file) {
                        if ($file->getExtension() == "csv") {
                            $translation_modules[] = basename($file->getFilename(), ".csv");
                        }
                    }
                }
            }
        }

        foreach ($translation_modules as $mod) {
            Core_Model_Translator::addModule($mod);
        }
    }

    /**
     * @param $feature_code
     * @param bool $refresh
     * @return mixed
     */
    public function getFeature($feature_code, $refresh = false)
    {
        $this->getFeatures($refresh);

        return $this->_features[$feature_code];
    }

    /**
     * @param bool $refresh
     * @return array|null
     * @throws Zend_Db_Profiler_Exception
     * @throws Zend_Db_Statement_Exception
     */
    public function getFeatures($refresh = false)
    {
        if ($this->_basePath === null) {
            $this->fetch();
        }

        if ($this->_features === null || $refresh) {
            $this->_features = [];

            $featuresGlob = glob($this->_basePath . '/features/*/feature.json');
            foreach ($featuresGlob as $feature) {
                $featureJson = Siberian_Json::decode(file_get_contents($feature), true);

                // We assume every module is not ES5 compliant unless it is tested with ES-CHECK later*
                // ES5 compliance means support for Android 7.1 and below
                $featureJson['es5_compliant'] = false;

                if ($featureJson) {
                    $featureJson['__JSON__'] = json_encode($featureJson);
                    $featureJson['__FILE__'] = $feature;
                    $featureJson['__DIR__'] = dirname($feature);

                    $mandatoryKeys = [
                        'name',
                        'code',
                        'model',
                        'desktop_uri',
                        'routes',
                        'icons'
                    ];

                    $valid = true;
                    foreach ($mandatoryKeys as $k) {
                        if (!array_key_exists($k, $featureJson)) {
                            $valid = false;
                            break;
                        }
                    }

                    if ($valid) {
                        $main_route = array_reduce( // Let's see if we have a mobile_uri
                            $featureJson['routes'],
                            static function ($carry, $item) {
                                return (array_key_exists('root', $item) && ($item['root'] === true)) ?
                                    $item['url'] : $carry;
                            },
                            null
                        );

                        if ($main_route) { // If we have, it definitely is a feature
                            $featureJson['mobile_uri'] = 'goto/feature/' . $featureJson['code'];
                            $this->_features[$featureJson['code']] = $featureJson;
                        } else {
                            // It's a service!
                            $this->_features[$featureJson['code']] = $featureJson;
                        }
                    }
                }
            }
        }

        return array_values($this->_features);
    }

}
