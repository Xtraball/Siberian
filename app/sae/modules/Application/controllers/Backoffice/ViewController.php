<?php
class Application_Backoffice_ViewController extends Backoffice_Controller_Default
{

    public function loadAction() {

        $html = array(
            "title" => $this->_("Application"),
            "icon" => "fa-mobile",
        );

        $this->_sendHtml($html);

    }

    public function findAction() {

        $application = Application_Model_Application::getInstance();

        $admin = new Admin_Model_Admin();

        if (Siberian_Version::is("sae")) {
            $admins = $admin->findAll()->toArray();
            $admin_owner = $admin;
            $admin_owner->setData(current($admins));
        } else {
            $admins = $admin->getAllApplicationAdmins($this->getRequest()->getParam("app_id"));
            $admin_owner = $application->getOwner();
        }

        $admin_list = array();
        foreach($admins as $admin) {
            $admin_list[] = $admin;
        }

        $admin = array(
            "name" => $admin_owner->getFirstname() . " " . $admin_owner->getLastname(),
            "email" => $admin_owner->getEmail(),
            "company" => $admin_owner->getCompany(),
            "phone" => $admin_owner->getPhone()
        );


        $store_categories = Application_Model_Device_Ionic_Ios::getStoreCategeories();
        $devices = array();
        foreach($application->getDevices() as $device) {
            $device->setName($device->getName());
            $device->setBrandName($device->getBrandName());
            $device->setStoreName($device->getStoreName());
            $device->setHasMissingInformation(
                !$device->getUseOurDeveloperAccount() &&
                (!$device->getDeveloperAccountUsername() || !$device->getDeveloperAccountPassword())
            );
            $devices[] = $device->getData();
        }

        $data = array(
            'admin' => $admin,
            'admin_list' => $admin_list,
            'app_store_icon' => $application->getAppStoreIcon(),
            'google_play_icon' => $application->getGooglePlayIcon(),
            'devices' => $devices,
            'url' => $application->getUrl(),
            'has_ios_certificate' => Push_Model_Certificate::getiOSCertificat() !== null
        );

        foreach($store_categories as $name => $store_category) {
            if($store_category->getId() == $application->getMainCategoryId()) $data['main_category_name'] = $name;
            else if($store_category->getId() == $application->getSecondaryCategoryId()) $data['secondary_category_name'] = $name;
        }

        $folder_name = $application->getDevice(2)->getTmpFolderName();
        $apk_path = null;
        $date_mod = null;        

        /** Ionic path */
        if($folder_name != "") {
            $apk_base_path = Core_Model_Directory::getBasePathTo("var/tmp/applications/ionic/android/{$folder_name}/build/outputs/apk/{$folder_name}-release.apk");
        }

        if(file_exists($apk_base_path)) {
            $apk_path = Core_Model_Directory::getPathTo("var/tmp/applications/ionic/android/{$folder_name}/build/outputs/apk/{$folder_name}-release.apk");
            $date = new Zend_Date(filemtime($apk_base_path),Zend_Date::TIMESTAMP);
            $date_mod = $date->toString($this->_("MM/dd/y 'at' hh:mm a"));
        }

        $data["bundle_id"] = $application->getBundleId();
        $data["is_active"] = $application->isActive();
        $data["is_locked"] = $application->isLocked();
        $data["can_be_published"] = $application->canBePublished();
        $data["owner_use_ads"] = !!$application->getOwnerUseAds();

        if($application->getFreeUntil()) {
            $date = new Zend_Date($application->getFreeUntil(), Zend_Date::ISO_8601);
            $data["free_until"] = $date->toString("MM/dd/yyyy");
        }

        $data["apk"] = array(
            "link" => $apk_path,
            'date' => $date_mod
        );
        $application->addData($data);
        $data = array(
            "application" => $application->getData(),
            'statuses' => Application_Model_Device::getStatuses(),
            'design_codes' => Application_Model_Application::getDesignCodes()
        );


        $this->_sendHtml($data);

    }

    public function saveAction() {

        if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {

                /** Unable to save angular application */
                if(isset($data["design_code"])) {
                    unset($data["design_code"]);
                }

                if(empty($data["app_id"])) {
                    throw new Exception($this->_("An error occurred while saving. Please try again later."));
                }

                $application = new Application_Model_Application();
                $application->find($data["app_id"]);

                if(!$application->getId()) {
                    throw new Exception($this->_("An error occurred while saving. Please try again later."));
                }

                if(isset($data["design_code"]) AND $application->getDesignCode() == Application_Model_Application::DESIGN_CODE_IONIC AND $data["design_code"] != Application_Model_Application::DESIGN_CODE_IONIC) {
                    throw new Exception($this->_("You can't go back to Angular."));
                }

                if(!empty($data["key"])) {

                    $module_names = array_map('strtolower', Zend_Controller_Front::getInstance()->getDispatcher()->getSortedModuleDirectories());
                    if(in_array($data["key"], $module_names)) {
                        throw new Exception($this->_("Your domain key \"%s\" is not valid.", $data["key"]));
                    }

                    $dummy = new Application_Model_Application();
                    $dummy->find($data["key"], "key");
                    if($dummy->getId() AND $dummy->getId() != $application->getId()) {
                        throw new Exception($this->_("The key is already used by another application."));
                    }
                } else {
                    throw new Exception($this->_("The key cannot be empty."));
                }

                if(!empty($data["domain"])) {

                    $data["domain"] = str_replace(array("http://", "https://"), "", $data["domain"]);

                    $tmp_url = str_replace(array("http://", "https://"), "", $this->getRequest()->getBaseUrl());
                    $tmp_url = current(explode("/", $tmp_url));

                    $tmp_domain = explode("/", $data["domain"]);
                    $domain = current($tmp_domain);
                    if(preg_match('/^(www.)?('.$domain.')/', $tmp_url)) {
                        throw new Exception($this->_("You can't use this domain."));
                    }else{
                        $domain_folder = next($tmp_domain);
                        $module_names = array_map('strtolower', Zend_Controller_Front::getInstance()->getDispatcher()->getSortedModuleDirectories());
                        if(in_array($domain_folder, $module_names)) {
                            throw new Exception($this->_("Your domain key \"%s\" is not valid.", $domain_folder));
                        }
                    }

                    if(!Zend_Uri::check("http://".$data["domain"])) {
                        throw new Exception($this->_("Please enter a valid URL"));
                    }

                    $dummy = new Application_Model_Application();
                    $dummy->find($data["domain"], "domain");
                    if($dummy->getId() AND $dummy->getId() != $application->getId()) {
                        throw new Exception("The domain is already used by another application.");
                    }

                }

                if(empty($data["free_until"])) {
                    $data["free_until"] = null;
                } else {
                    $data["free_until"] = new Zend_Date($data["free_until"], "MM/dd/yyyy");
                    $data["free_until"] = $data["free_until"]->toString('yyyy-MM-dd HH:mm:ss');
                }

                $application->addData($data)->save();

                $data = array(
                    "success"   => 1,
                    "message"   => $this->_("Info successfully saved"),
                    "bundle_id" => $application->getBundleId(),
                    "url"       => $application->getUrl(),
                );

            } catch(Exception $e) {
                $data = array(
                    "error" => 1,
                    "message" => $e->getMessage()
                );
            }

            $this->_sendHtml($data);
        }

    }

    public function switchionicAction() {
        if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {

                if(empty($data["app_id"])) {
                    throw new Exception($this->_("An error occurred while saving. Please try again later."));
                }

                if(isset($data["design_code"]) && $data["design_code"] != Application_Model_Application::DESIGN_CODE_IONIC) {
                    throw new Exception($this->_("You can't go back with Angular."));
                }

                $application = new Application_Model_Application();
                $application->find($data["app_id"]);

                $application->setDesignCode(Application_Model_Application::DESIGN_CODE_IONIC);

                if($design_id = $application->getDesignId()) {

                    $design = new Template_Model_Design();
                    $design->find($design_id);

                    if($design->getId()) {
                        $application->setDesign($design);
                        Template_Model_Design::generateCss($application);
                    }

                }

                $application->save();

                $data = array(
                    "success"   => 1,
                    "message"   => $this->_("Your application is now switched to Ionic"),
                    "design_code" => "ionic",
                );

            } catch(Exception $e) {
                $data = array(
                    "error" => 1,
                    "message" => $e->getMessage()
                );
            }

            $this->_sendHtml($data);
        }
    }


    public function savedeviceAction() {

        if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {

                if(empty($data["app_id"]) OR !is_array($data["devices"]) OR empty($data["devices"])) {
                    throw new Exception($this->_("An error occurred while saving. Please try again later."));
                }

                $application = new Application_Model_Application();
                $application->find($data["app_id"]);

                if(!$application->getId()) {
                    throw new Exception($this->_("An error occurred while saving. Please try again later."));
                }

                foreach($data["devices"] as $device_data) {
                    if(!empty($device_data["store_url"])) {
                        if(stripos($device_data["store_url"], "http") === false) {
                            $device_data["store_url"] = "http://".$device_data["store_url"];
                        }
                        if(!Zend_Uri::check($device_data["store_url"])) {
                            throw new Exception($this->_("Please enter a correct URL for the %s store", $device_data["name"]));
                        }
                    } else {
                        $device_data["store_url"] = null;
                    }

                    $device = $application->getDevice($device_data["type_id"]);
                    $device->addData($device_data)->save();
                }

                $data = array(
                    "success" => 1,
                    "message" => $this->_("Info successfully saved")
                );

            } catch(Exception $e) {
                $data = array(
                    "error" => 1,
                    "message" => $e->getMessage()
                );
            }

            $this->_sendHtml($data);
        }

    }

    public function saveadvertisingAction() {

        if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {

                if(empty($data["app_id"]) OR !is_array($data["devices"]) OR empty($data["devices"])) {
                    throw new Exception($this->_("An error occurred while saving. Please try again later."));
                }

                $application = new Application_Model_Application();
                $application->find($data["app_id"]);

                if(!$application->getId()) {
                    throw new Exception($this->_("An error occurred while saving. Please try again later."));
                }

                $data_app_to_save = array(
                    "owner_use_ads" => $data["owner_use_ads"]
                );

                $application->addData($data_app_to_save)->save();

                foreach($data["devices"] as $device_data) {
                    $device = $application->getDevice($device_data["type_id"]);
                    $data_device_to_save = array(
                        "owner_admob_id" => $device_data["owner_admob_id"],
                        "owner_admob_type" => $device_data["owner_admob_type"]
                    );
                    $device->addData($data_device_to_save)->save();
                }

                $data = array(
                    "success" => 1,
                    "message" => $this->_("Info successfully saved")
                );

            } catch(Exception $e) {
                $data = array(
                    "error" => 1,
                    "message" => $e->getMessage()
                );
            }

            $this->_sendHtml($data);
        }
    }

    public function savebannerAction() {

        if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {

                if(empty($data["app_id"]) OR !is_array($data["devices"]) OR empty($data["devices"])) {
                    throw new Exception($this->_("An error occurred while saving. Please try again later."));
                }

                $application = new Application_Model_Application();
                $application->find($data["app_id"]);

                if(!$application->getId()) {
                    throw new Exception($this->_("An error occurred while saving. Please try again later."));
                }

                $data_app_to_save = array(
                    "banner_title" => $data["banner_title"],
                    "banner_author" => $data["banner_author"],
                    "banner_button_label" => $data["banner_button_label"]
                );

                $application->addData($data_app_to_save)->save();

                foreach($data["devices"] as $device_data) {
                    $device = $application->getDevice($device_data["type_id"]);
                    $data_device_to_save = array(
                        "banner_store_label" => $device_data["banner_store_label"],
                        "banner_store_price" => $device_data["banner_store_price"]
                    );
                    $device->addData($data_device_to_save)->save();
                }

                $data = array(
                    "success" => 1,
                    "message" => $this->_("Info successfully saved")
                );

            } catch(Exception $e) {
                $data = array(
                    "error" => 1,
                    "message" => $e->getMessage()
                );
            }

            $this->_sendHtml($data);
        }
    }

    public function downloadsourceAction() {

        if($data = $this->getRequest()->getParams()) {

            try {

                $application = new Application_Model_Application();

                if(empty($data['app_id']) OR empty($data['device_id'])) {
                    throw new Exception($this->_('This application does not exist'));
                }

                $application->find($data['app_id']);
                if(!$application->getId()) {
                    throw new Exception($this->_('This application does not exist'));
                }

                if($design_code = $this->getRequest()->getParam("design_code")) {
                    $application->setDesignCode($design_code);
                }

                $device = $application->getDevice($data["device_id"]);
                $device->setApplication($application);
                $device->setDownloadType($this->getRequest()->getParam("type"))
                    ->setExcludeAds($this->getRequest()->getParam("no_ads"))
                ;
                $zip = $device->getResources();

                if($this->getRequest()->getParam("type") != "apk") {
                    $path = explode('/', $zip);
                    end($path);
                    $this->_download($zip, current($path), 'application/octet-stream');
                } else {
                    die;
                }

            }
            catch(Exception $e) {
                Zend_Registry::get("logger")->sendException(print_r($e, true), "source_generator_", false);
                if($application->getId()) {
                    $this->_redirect('application/backoffice_view', array("app_id" => $application->getId()));
                } else {
                    $this->_redirect('application/backoffice_list');
                }
            }

        }

    }

    public function uploadcertificateAction() {

        if($app_id = $this->getRequest()->getParam("app_id")) {

            try {

                if (empty($_FILES) || empty($_FILES['file']['name'])) {
                    throw new Exception("No file has been sent");
                }

                $application = new Application_Model_Application();
                $application->find($app_id);

                $base_path = Core_Model_Directory::getBasePathTo("var/apps/iphone/");
                if(!is_dir($base_path)) mkdir($base_path, 0775, true);
                $path = Core_Model_Directory::getPathTo("var/apps/iphone/");
                $adapter = new Zend_File_Transfer_Adapter_Http();
                $adapter->setDestination(Core_Model_Directory::getTmpDirectory(true));

                if ($adapter->receive()) {

                    $file = $adapter->getFileInfo();

                    $certificat = new Push_Model_Certificate();
                    $certificat->find(array('type' => 'ios', 'app_id' => $app_id));

                    if(!$certificat->getId()) {
                        $certificat->setType("ios")
                            ->setAppId($app_id)
                        ;
                    }

                    $new_name = uniqid("cert_").".pem";
                    if(!rename($file["file"]["tmp_name"], $base_path.$new_name)) {
                        throw new Exception($this->_("An error occurred while saving. Please try again later."));
                    }

                    $certificat->setPath($path.$new_name)
                        ->save()
                    ;

                    $data = array(
                        "success" => 1,
                        "message" => $this->_("The file has been successfully uploaded")
                    );

                } else {
                    $messages = $adapter->getMessages();
                    if (!empty($messages)) {
                        $message = implode("\n", $messages);
                    } else {
                        $message = $this->_("An error occurred during the process. Please try again later.");
                    }

                    throw new Exception($message);
                }
            } catch (Exception $e) {
                $data = array(
                    "error" => 1,
                    "message" => $e->getMessage()
                );
            }

            $this->_sendHtml($data);

        }

    }

}
