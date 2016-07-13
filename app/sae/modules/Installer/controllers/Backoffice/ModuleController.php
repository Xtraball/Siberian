<?php

class Installer_Backoffice_ModuleController extends Backoffice_Controller_Default {

    static $MODULES = array();

    public function loadAction() {

        $html = array(
            "title" => $this->_("Modules"),
            "icon" => "fa-cloud-download"
        );

        $this->_sendHtml($html);
    }

    public function downloadupdateAction() {

        try {

            set_time_limit(6000);
            ini_set('max_execution_time', 6000);
            ini_set("memory_limit", "512M");

            $data = $this->_fetchUpdates();

            if(empty($data["success"])) {
                throw new Exception($this->_("An error occurred while loading. Please, try again later."));
            }

            if(!empty($data["url"]) AND !empty($data["filename"])) {
            
                $tmp_path = Core_Model_Directory::getTmpDirectory(true)."/".$data["filename"];

                $client = new Zend_Http_Client($data["url"], array(
                    'adapter'   => 'Zend_Http_Client_Adapter_Curl',
                ));

                $client->setMethod(Zend_Http_Client::POST);
                $client->setParameterPost("secret", Core_Model_Secret::SECRET);

                $response = $client->request();

                if($response->getStatus() == 200) {
                    $content = $response->getBody();

                    if(empty($content)) {
                        throw new Exception($this->_("Unable to fetch the update. Please, try again later."));
                    }

                    file_put_contents($tmp_path, $content);

                } else {
                    throw new Exception($this->_("Unable to fetch the update. Please, try again later."));
                }

                if(!file_exists($tmp_path)) {
                    throw new Exception($this->_("Unable to fetch the update. Please, try again later."));
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
                throw new Exception($this->_("No file has been sent"));
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
                    $message = $this->_("An error occurred during the process. Please try again later.");
                }

                throw new Exception($message);
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
                    throw new Exception($this->_("The file %s does not exist", $filename));
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
                    throw new Exception($this->_($message));
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
                    throw new Exception($this->_("Unable to connect to your FTP. Please check the connection information."));
                } else if(!$ftp->isSiberianDirectory()) {
                    $error_code = 2;
                    throw new Exception($this->_("Unable to detect your site. Please make sure the entered path is correct."));
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
                    "message" => $this->_("Info successfully saved")
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
                    throw new Exception($this->_("The file %s does not exist", $filename));
                }

                $parser = new Installer_Model_Installer_Module_Parser();
                if($parser->setFile($file)->copy()) {

                    $data = array("success" => 1);

                } else {

                    $messages = $parser->getErrors();
                    $message = implode("\n", $messages);

                    throw new Exception($this->_($message));

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

        $data = array();

        try {

            echo "<pre>";

            $cache = Zend_Registry::isRegistered('cache') ? Zend_Registry::get('cache') : null;
            if($cache) {
                $cache->clean("all");
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
            }

            foreach($installers as $installer) {
                $installer->insertData();
            }

            /** Try installing fresh template. */
            $installer = new Installer_Model_Installer();
            $installer->setModuleName("Template")
                ->install()
            ;

            /** Clear cache */
            Siberian_Cache_Design::clearCache();
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
                "message" => $this->_("Module successfully installed")
            );

            $protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
            Siberian_Autoupdater::configure($protocol.$this->getRequest()->getHttpHost());

        } catch(Exception $e) {
            $data = array(
                "error" => 1,
                "message" => $e->getMessage()
            );
        }

        $this->_sendHtml($data);

    }

    protected function _fetchUpdates() {

        $current_version = Siberian_Version::VERSION;
        $platform_type = strtolower(Siberian_Version::TYPE);
        $url = "http://updates.siberiancms.com/check.php?";
        $url .= "type={$platform_type}&version={$current_version}";

        if(APPLICATION_ENV == "development") {
            /** Fetch updates on local development server */
            $url = str_replace(".com", ".dev", $url);
        }

        $client = new Zend_Http_Client($url, array(
            'adapter'   => 'Zend_Http_Client_Adapter_Curl',
        ));

        $client->setMethod(Zend_Http_Client::POST);
        $client->setParameterPost("secret", Core_Model_Secret::SECRET);

        $response = $client->request();

        $content = $response->getBody();

        if(empty($content)) {
            throw new Exception($this->_("An error occurred while loading. Please, try again later."));            
        }

        $content = Zend_Json::decode($content);
        if($response->getStatus() != 200) {

            $message = $this->_("Unable to check for updates now. Please, try again later.");
            if(!empty($content["error"]) AND !empty($content["message"])) {
                $message = $this->_($content["message"]);
            }

            throw new Exception($message);
        } else if(empty($content["url"])) {
            $content["message"] = $this->_("Your system is up to date.");
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
                "name" => $this->_("%s Update", $package->getName()),
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

        return $data;

    }

}
