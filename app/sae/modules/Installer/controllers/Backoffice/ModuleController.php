<?php

class Installer_Backoffice_ModuleController extends Backoffice_Controller_Default {

    /**
     * @var array
     */
    static $MODULES = array();

    /**
     * @var bool
     */
    public $increase_timelimit = false;

    public function loadAction() {

        if(class_exists("Core_Model_Statistics")) {
            $stats = new Core_Model_Statistics();
            $stats->statistics();
        }

        $config = new System_Model_Config();
        $configs = $config->findAll(array(new Zend_Db_Expr('code LIKE "ftp_%"')));
        
        $html = array(
            "title" => __("Modules"),
            "icon" => "fa-cloud-download"
        );

        // Duplicate user to new namespace.
        if(version_compare(Siberian_Version::VERSION, "5.0.0", "<")) {
            $current_user = $this->getSession()->getBackofficeUser();
            $this->getSession()->setBackofficeUser($current_user);
        }

        $this->_sendHtml($html);

    }

    public function downloadupdateAction() {

        try {

            $fatal_errors = false;
            $_errors = array();

            if(function_exists("exec")) {

                // Testing zip/unzip
                $base = Core_Model_Directory::getBasePathTo("/var/tmp/");
                $zip_file = Core_Model_Directory::getBasePathTo("/var/tmp/test.zip");
                $test_file = Core_Model_Directory::getBasePathTo("/var/tmp/test.file");

                if(file_exists($test_file)) {
                    unlink($test_file);
                }
                if(file_exists($zip_file)) {
                    unlink($zip_file);
                }

                try {
                    file_put_contents($test_file, "test");
                    chdir($base);
                    exec("zip test.zip test.file");
                    if(!file_exists($zip_file)) {
                        $_errors[] = "Please enable/add binary: zip & unzip";
                        $fatal_errors = true;
                    } else {
                        // now test unzip
                        if(file_exists($test_file)) {
                            unlink($test_file);
                        }
                        exec("unzip {$zip_file}");
                        if(!file_exists($test_file)) {
                            $_errors[] = "Please enable/add binary: unzip";
                            $fatal_errors = true;
                        }
                    }
                } catch(Exception $e) {
                    $_errors[] = "Please enable/add binary: zip";
                    $fatal_errors = true;
                } finally {
                    // Unlink files
                    if(file_exists($test_file)) {
                        unlink($test_file);
                    }
                    if(file_exists($zip_file)) {
                        unlink($zip_file);
                    }
                }


            } else {
                $_errors[] = "Please enable/add function: exec()";
                $fatal_errors = true;
            }

            if($fatal_errors) {
                throw new Siberian_Exception(implode(", ", $_errors));
            }

            set_time_limit(6000);
            ini_set('max_execution_time', 6000);
            ini_set("memory_limit", "512M");

            $data = $this->_fetchUpdates();

            log_debug(print_r($data, true));

            if(empty($data["success"])) {
                throw new Siberian_Exception(__("An error occurred while loading. Please, try again later."));
            }

            if(!empty($data["url"]) AND !empty($data["filename"])) {

                $tmp_path = Core_Model_Directory::getTmpDirectory(true)."/".$data["filename"];

                # for hotfix ssl
                $client = new Zend_Http_Client($data["url"], array(
                    'adapter'   => 'Zend_Http_Client_Adapter_Curl',
                    'curloptions' => array(CURLOPT_SSL_VERIFYPEER => false),
                ));

                $client->setMethod(Zend_Http_Client::POST);

                if(Siberian_Version::TYPE === "SAE") {
                    $client->setParameterPost("sae", 1);
                } else {
                    $license_key = System_Model_Config::getValueFor("siberiancms_key");
                    if(!$license_key) {
                        throw new Siberian_Exception(__("There is no CMS license key set."));
                    }
                    $client->setParameterPost("licenseKey", $license_key);
                    $client->setParameterPost("host", $_SERVER["HTTP_HOST"]);
                }

                $response = $client->request();

                if($response->getStatus() == 200) {
                    $content = $response->getBody();

                    if(empty($content)) {
                        throw new Siberian_Exception(__("#100: Unable to fetch the update. Please, try again later."));
                    }

                    file_put_contents($tmp_path, $content);

                } else {
                    throw new Siberian_Exception(__("#101: Unable to fetch the update. Please, try again later."));
                }

                if(!file_exists($tmp_path)) {
                    throw new Siberian_Exception(__("#102: Unable to fetch the update. Please, try again later."));
                }

                $data = $this->_getPackageDetails($tmp_path);
            }

        } catch(Exception $e) {
            $data = array(
                "error" => 1,
                "message" => $e->getMessage()
            );
        }

        $this->_sendHtml($data);

    }

    public function uploadAction() {

        try {

            if(empty($_FILES) || empty($_FILES['file']['name'])) {
                throw new Siberian_Exception(__("No file has been sent"));
            }

            $adapter = new Zend_File_Transfer_Adapter_Http();
            $adapter->setDestination(Core_Model_Directory::getTmpDirectory(true));

            if($adapter->receive()) {

                $file = $adapter->getFileInfo();

                $data = $this->_getPackageDetails($file['file']['tmp_name']);

            } else {
                $messages = $adapter->getMessages();
                if(!empty($messages)) {
                    $message = implode("\n", $messages);
                } else {
                    $message = __("An error occurred during the process. Please try again later.");
                }

                throw new Siberian_Exception($message);
            }
        } catch(Exception $e) {
            $data = array(
                "error" => 1,
                "message" => $e->getMessage()
            );
        }

        $this->_sendHtml($data);
    }

    public function checkpermissionsAction() {

        if($file = $this->getRequest()->getParam("file")) {

            $data = array();

            try {

                $filename = base64_decode($file);
                $file = Core_Model_Directory::getTmpDirectory(true)."/$filename";

                if(!file_exists($file)) {
                    throw new Siberian_Exception(__("The file %s does not exist", $filename));
                }

                $parser = new Installer_Model_Installer_Module_Parser();
                $is_ok = $parser->setFile($file)->checkPermissions();

                if(!$is_ok) {
                    $ftp_host = System_Model_Config::getValueFor("ftp_host");
                    $ftp_user = System_Model_Config::getValueFor("ftp_username");
                    $ftp_password = System_Model_Config::getValueFor("ftp_password");
                    $ftp_port = System_Model_Config::getValueFor("ftp_port");
                    $ftp_path = System_Model_Config::getValueFor("ftp_path");
                    $ftp = new Siberian_Ftp($ftp_host, $ftp_user, $ftp_password, $ftp_port, $ftp_path);

                    if($ftp->checkConnection() AND $ftp->isSiberianDirectory()) {
                        $is_ok = true;
                    }
                }

                if($is_ok) {
                    $data = array("success" => 1);
                } else {

                    $messages = $parser->getErrors();
                    $message = implode("\n", $messages);
                    throw new Siberian_Exception(__($message));
                }

            } catch(Exception $e) {
                $data = array(
                    "error" => 1,
                    "message" => $e->getMessage()
                );
            }

            $this->_sendHtml($data);
        }

    }

    public function saveftpAction() {

        if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {

                $error_code = 0;
                $ftp_host = !empty($data["host"]) ? $data["host"] : null;
                $ftp_user = !empty($data["username"]) ? $data["username"] : null;
                $ftp_password = !empty($data["password"]) ? $data["password"] : null;
                $ftp_port = !empty($data["port"]) ? $data["port"] : Siberian_Ftp::DEFAULT_PORT;
                $ftp_path = null;

                if(!empty($data["path"])) {
                    $ftp_path = rtrim($data["path"], "/");
                }
                if(!$ftp_path) {
                    $ftp_path = Siberian_Ftp::DEFAULT_PATH;
                }

                $ftp = new Siberian_Ftp($ftp_host, $ftp_user, $ftp_password, $ftp_port, $ftp_path);
                if(!$ftp->checkConnection()) {
                    $error_code = 1;
                    throw new Siberian_Exception(__("Unable to connect to your FTP. Please check the connection information."));
                } else if(!$ftp->isSiberianDirectory()) {
                    $error_code = 2;
                    throw new Siberian_Exception(__("Unable to detect your site. Please make sure the entered path is correct."));
                }

                $fields = array(
                    "ftp_host" => $ftp_host,
                    "ftp_username" => $ftp_user,
                    "ftp_password" => $ftp_password,
                    "ftp_port" => $ftp_port,
                    "ftp_path" => $ftp_path,
                );

                foreach($fields as $key => $value) {
                    $config = new System_Model_Config();
                    $config->find($key, "code");

                    if(!$config->getId()) {
                        $config->setCode($key)
                            ->setLabel(ucfirst(implode(" ", explode("_", $key))))
                        ;
                    }

                    $config->setCode($key)
                        ->setValue($value)
                        ->save()
                    ;
                }

                $data = array(
                    "success" => 1,
                    "message" => __("Info successfully saved")
                );

            } catch(Exception $e) {
                $data = array(
                    "error" => 1,
                    "code" => $error_code,
                    "message" => $e->getMessage()
                );
            }

            $this->_sendHtml($data);
        }

    }

    public function copyAction() {

        if($file = $this->getRequest()->getParam("file")) {

            $data = array();

            try {

                $filename = base64_decode($file);
                $file = Core_Model_Directory::getTmpDirectory(true)."/$filename";

                if(!file_exists($file)) {
                    throw new Siberian_Exception(__("The file %s does not exist", $filename));
                }

                $parser = new Installer_Model_Installer_Module_Parser();
                if($parser->setFile($file)->copy()) {

                    $data = array("success" => 1);

                } else {

                    $messages = $parser->getErrors();
                    $message = implode("\n", $messages);

                    throw new Siberian_Exception(__($message));

                }

            } catch(Exception $e) {
                $data = array(
                    "error" => 1,
                    "message" => $e->getMessage()
                );
            }

            $this->_sendHtml($data);
        }

    }

    public function installAction() {

        # Increase the timelimit to ensure update will finish
        //$this->increase_timelimit = set_time_limit(300);

        $data = array();

        try {

            $cache = Zend_Registry::isRegistered('cache') ? Zend_Registry::get('cache') : null;
            if($cache) {
                $cache->clean(Zend_Cache::CLEANING_MODE_ALL);
            }

            $cache_ids = array('js_mobile.js', 'js_desktop.js', 'css_mobile.css', 'css_desktop.css');
            foreach ($cache_ids as $cache_id) {
                if(file_exists(Core_Model_Directory::getCacheDirectory(true)."/{$cache_id}")) {
                    unlink(Core_Model_Directory::getCacheDirectory(true)."/{$cache_id}");
                }
            }

            $module_names = Zend_Controller_Front::getInstance()->getDispatcher()->getSortedModuleDirectories();
            self::$MODULES = array();
            foreach($module_names as $module_name) {
                $module = new Installer_Model_Installer_Module();
                $module->prepare($module_name);
                if($module->canUpdate()) {
                    self::$MODULES[] = $module->getName();
                }
            }

            self::$MODULES = array_unique(self::$MODULES);

            $installers = array();
            foreach(self::$MODULES as $module) {
                $installer = new Installer_Model_Installer();
                $installer->setModuleName($module)
                    ->install()
                ;

                $installers[] = $installer;

                # Try to increase max execution time (if the set failed)
                $this->_signalRetry();
            }

            foreach($installers as $installer) {
                $installer->insertData();

                # Try to increase max execution time (if the set failed)
                $this->_signalRetry();
            }

            /** Try installing fresh template. */
            $installer = new Installer_Model_Installer();
            $installer->setModuleName("Template")
                ->install()
            ;

            /** Clear cache */
            Siberian_Cache_Design::clearCache();
            Siberian_Cache_Translation::clearCache();
            Siberian_Minify::clearCache();

            $host = $this->getRequest()->getHeader("host");
            if($host AND $host == base64_decode("YXBwcy5tb2JpdXNjcy5jb20=")) {
                $email = base64_decode("Y29udGFjdEBzaWJlcmlhbmNtcy5jb20=");
                $object = "$host - Siberian Update";
                $message = "Siberian " . Siberian_Version::NAME . " " . Siberian_Version::VERSION;
                mail($email, $object, $message);
            }

            $data = array(
                "success" => 1,
                "message" => __("Module successfully installed")
            );

            # Try to increase max execution time (if the set failed)
            $this->_signalRetry();

            $protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
            Siberian_Autoupdater::configure($protocol.$this->getRequest()->getHttpHost());

            $cron_model = new Cron_Model_Cron();
            $cachebuilder = $cron_model->find("cachebuilder", "command");

            if($cachebuilder->getId()) {
                $options = array(
                    "host" => $protocol.$this->getRequest()->getHttpHost(),
                );
                $cachebuilder->setOptions(Siberian_Json::encode($options))->save();
                $cachebuilder->enable();
            }

        } catch(Siberian_Exec_Exception $e) {
            $data = array(
                "success" => 1,
                "reached_timeout" => true,
                "message" => $e->getMessage()
            );
        } catch(Exception $e) {
            $data = array(
                "error" => 1,
                "message" => $e->getMessage()
            );
        }

        $this->_sendHtml($data);

    }

    /**
     * Detect if we are close to the timeout and send a signal to continue the installation process.
     *
     * @todo remove class_exists("Siberian_Exec") after 4.8.7
     */
    protected function _signalRetry() {
        if(class_exists("Siberian_Exec") && !$this->increase_timelimit) {
            if(Siberian_Exec::willReachMaxExecutionTime(5)) {
                throw new Siberian_Exec_Exception("Installation will continue, please wait ...");
            }
        }
    }

    protected function _fetchUpdates() {

        /** Default updates url in case of missing configuration */
        $updates_url = "https://updates02.siberiancms.com";

        $update_channel = System_Model_Config::getValueFor("update_channel");
        if(in_array($update_channel, array("stable", "beta", "preview"))) {
            switch($update_channel) {
                case "stable":
                    $updates_url = "https://updates02.siberiancms.com";
                    break;
                case "beta":
                    $updates_url = "https://beta-updates02.siberiancms.com";
                    break;
                case "preview":
                    $updates_url = "https://preview-updates02.siberiancms.com";
                    break;
            }
        }

        $current_version = Siberian_Version::VERSION;

        $url = "{$updates_url}/check.php?";
        $url .= "version={$current_version}";

        $client = new Zend_Http_Client($url, array(
            'adapter'   => 'Zend_Http_Client_Adapter_Curl',
            'curloptions' => array(CURLOPT_SSL_VERIFYPEER => false),
        ));
        $client->setMethod(Zend_Http_Client::POST);

        if(Siberian_Version::TYPE === "SAE") {
            $client->setParameterPost("sae", "1");
        } else {
            $license_key = System_Model_Config::getValueFor("siberiancms_key");
            if(!$license_key) {
                throw new Siberian_Exception(__("There is no CMS license key set."));
            }
            $client->setParameterPost("licenseKey", $license_key);
            $client->setParameterPost("host", $_SERVER["HTTP_HOST"]);
        }

        $response = $client->request();

        $content = $response->getBody();

        if(empty($content)) {
            throw new Siberian_Exception(__("An error occurred while loading. Please, try again later."));
        }

        $content = Zend_Json::decode($content);
        if($response->getStatus() != 200) {

            $message = __("Unable to check for updates now. Please, try again later.");
            if(!empty($content["error"]) AND !empty($content["message"])) {
                $message = __($content["message"]);
            }

            throw new Siberian_Exception($message);
        } else if(empty($content["url"])) {
            $content["message"] = __("Your system is up to date.");
        }

        return $content;

    }

    protected function _getPackageDetails($file) {

        $installer = new Installer_Model_Installer();
        $installer->parse($file);

        $package = $installer->getPackageDetails();

        $path = pathinfo($file);
        $filename = $path["filename"].".".$path["extension"];

        $data = array(
            "success" => 1,
            "filename" => base64_encode($filename),
            "package_details" => array(
                "name" => __("%s Update", $package->getName()),
                "version" => $package->getVersion(),
                "description" => $package->getDescription()
            )
        );

        $data["release_note"] = array(
            "url" => false,
            "show" => false,
        );

        if(($release_note = $package->getReleaseNote())) {
            $data["release_note"] = $package->getReleaseNote();
        }

        $data["package_details"]["restore_apps"] = false;
        if(($restore_apps = $package->getRestoreApps())) {
            $data["package_details"]["restore_apps"] = $package->getRestoreApps();
        }

        return $data;

    }

}
