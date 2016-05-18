<?php

class Installer_Model_Installer_Module extends Core_Model_Default
{

    const DEFAULT_VERSION = '0.0.1';

    public $_lastVersion;

    protected $_name;
    protected $_dbFiles = array();
    protected $_schemaFiles = array();
    protected $_isInstalled = false;
    protected $_packageInfo = null;
    protected $_basePath;

    public function __construct($config = array()) {
        $this->_db_table = 'Installer_Model_Db_Table_Installer_Module';
        parent::__construct($config);
    }

    public function prepare($name) {

        $this->_name = $name;
        $this->findByName($name);

        if(!$this->getId()) {
            $this->setName($name)
                ->setVersion(self::DEFAULT_VERSION)
            ;
            $this->_isInstalled = false;
        }
        else {
            $this->_isInstalled = true;
        }

        $this->fetchModule($name);

        return $this;
    }

    public function reset() {
        $this->_lastVersion = null;
        $this->_dbFiles = array();
        $this->_isInstalled = false;
        $this->_basePath = null;
        return $this;
    }

    public function findByName($name) {

        if($this->getTable()->isInstalled()) {
            $this->find($name, 'Name');
        }

        return $this;
    }

    public function getName() {
        return $this->_name;
    }

    public function isInstalled() {
        return $this->_isInstalled;
    }

    public function canUpdate() {
        return version_compare($this->_lastVersion, $this->getVersion(), '>');
    }

    public function install() {

        if($this->canUpdate()) {

            /** Syncing DB only if needed */
            $migration_tables = array();
            foreach ($this->_schemaFiles as $table_name => $filename) {
                $migration_table = new Siberian_Migration_Db_Table($table_name);
                $migration_table->setSchemaPath($filename);
                $migration_table->tableExists();
                /** Test if table exist, if yes try to update, otherwise, try to create. */
                $migration_tables[] = $migration_table;
            }

            /** Dependencies injector (mainly for installation purpose) */
            if(isset($this->_packageInfo["dependencies"]["modules"])) {
                foreach($this->_packageInfo["dependencies"]["modules"] as $module => $version) {
                    $depModule = new Installer_Model_Installer_Module();
                    $depModule->prepare($module);

                    if(!$depModule->isInstalled()) {
                        $depModule->install();
                    }

                    if(!version_compare($depModule->_lastVersion, $version, ">=")) {
                        throw new Exception("The installed module {$module}@{$depModule->getVersion()} does not satisfy required {$module}@{$version}, aborting.");
                    }
                }
            }

            /** Now update the foreign keys. */
            foreach ($migration_tables as $table) {
                $table->updateForeignKeys();
            }

        }

        # Testing if it's a Template installer
        $template_install_path = Core_Model_Directory::getBasePathTo('var/tmp/template.install.php');
        if(is_readable($template_install_path)) {
            $this->_installTemplate($template_install_path);
            unlink($template_install_path);
        }
    }

    public function insertData() {
        /**
         * Processing data files (install or update)
         */
        foreach($this->_dbFiles as $version => $file) {
            if(version_compare($version, $this->getVersion(), '>')) {
                $this->_run($file, $version);
                $this->save();
            }
        }

        /** Set the version to the last in package.json */
        if(version_compare($this->_lastVersion, $this->getVersion(), '>')) {
            $this->setVersion($this->_lastVersion)->save();
        }
    }

    protected function readPackage($path) {
        $content = file_get_contents($path);

        if(!empty($content)) {
            return json_decode($content, true);
        }

        return false;
    }

    /** Fetching from sae to local */
    protected function fetchModule($module_name) {
        $basePath = Core_Model_Directory::getBasePathTo("app/sae/modules/{$module_name}");
        $editions = Siberian_Design::$editions[strtolower(Siberian_Version::TYPE)];

        $versions = array();
        $installer = array("version" => "0.0.0");

        /** fetching package.json */
        $package_info = false;
        foreach($editions as $edition) {
            $folder = str_replace("/sae/", "/$edition/", $basePath);
            if(is_readable($folder."/package.json")) {
                $package_info = $this->readPackage($folder."/package.json");
                # Don't break in case another package.json exists
            }
        }

        if(!$package_info) {
            trigger_error("Error: 'package.json' is missing from {$module_name}, unable to install/update the Module.", E_USER_WARNING);
            return;
        }

        $this->_packageInfo = $package_info;

        /** Get the schema for installation/sync */
        foreach($editions as $edition) {
            $folder = str_replace("/sae/", "/$edition/", $basePath);
            if(is_readable($folder."/resources/db/schema")) {
                $files = new DirectoryIterator($folder."/resources/db/schema");
                foreach($files as $file) {
                    if(!$file->isDot()) {
                        $table_name = str_replace(".php", "", basename($file->getFilename()));
                        //if(!isset($this->_schemaFiles[$table_name])) {
                            # Higher schema should override.
                            $this->_schemaFiles[$table_name] = $folder."/resources/db/schema/".$file->getFilename();
                        //}
                    }
                }
            }
        }

        /** First round for the installer */
        foreach($editions as $edition) {
            $folder = str_replace("/sae/", "/$edition/", $basePath);
            if(is_readable($folder."/resources/db/data")) {
                $files = new DirectoryIterator($folder."/resources/db/data");
                foreach($files as $file) {
                    /** Installer if needed */
                    if(!$this->isInstalled() && preg_match("/^install\.php$/", $file->getFilename())) {

                        $version = $package_info["version"];
                        if(version_compare($version, $installer["version"]) > 0) {
                            $installer = array(
                                "version" => $version,
                                "path" => $file->getPathName()
                            );
                        }
                    }
                }
            }

        }

        /** Second round for the updates */
        $version_updates = ($this->isInstalled()) ? $this->getVersion() : $installer["version"];

        foreach($editions as $edition) {
            $folder = str_replace("/sae/", "/$edition/", $basePath);
            if(is_readable($folder."/resources/db/data")) {
                $files = new DirectoryIterator($folder."/resources/db/data");
                foreach($files as $file) {

                    /** Database & Template updates */
                    if(preg_match("/^([0-9\.]*)\.php$/", $file->getFilename())) {

                        $version = str_replace(".php", "", $file->getFilename());
                        if(version_compare($version, $version_updates, ">") > 0) {

                            $this->_dbFiles[$version] = $file->getPathName();
                            if(!isset($versions[$version])) {
                                $versions[] = $version;
                            }
                        }
                    }
                }

                if(!empty($installer["path"])) {
                    $this->_dbFiles[$installer["version"]] = $installer["path"];
                    $versions[] = $installer["version"];
                }

                uksort($this->_dbFiles, "version_compare");
                usort($versions, "version_compare");
            }

        }

        $this->_lastVersion = $package_info["version"];


    }

    protected function _run($file, $version) {

        try {
            $this->getTable()->install($this->getName(), $file, $version);
            $this->setVersion($version);
        }
        catch(Exception $e) {
            $logger = Zend_Registry::get("logger");
            $logger->sendException("Fatal Error When Connecting to The Database: \n".print_r($e, true));
        }
        return $this;
    }

    protected function _installTemplate($file) {
        try {
            $this->getTable()->install("", $file, "");
        }
        catch(Exception $e) {
            $logger = Zend_Registry::get("logger");
            $logger->sendException("Fatal Error When Connecting to The Database: \n".print_r($e, true));
        }
        return $this;
    }

}