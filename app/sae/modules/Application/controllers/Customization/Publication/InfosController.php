<?php

class Application_Customization_Publication_InfosController extends Application_Controller_Default {

    /**
     * @var array
     */
    public $cache_triggers = array(
        "save" => array(
            "tags" => array("app_#APP_ID#"),
        ),
        "switchtoionic" => array(
            "tags" => array("app_#APP_ID#"),
        ),
    );

    public function indexAction() {
        $this->loadPartials();

        if($this->getRequest()->isXmlHttpRequest()) {
            $html = array('html' => $this->getLayout()->getPartial('content_editor')->toHtml());
            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function saveAction() {

        if($data = $this->getRequest()->getPost()) {

            try {


                if(!empty($data["name"])) {
                    if(is_numeric(substr($data["name"], 0, 1))) {
                        throw new Exception("The application's name cannot start with a number");
                    }
                    $this->getApplication()->setName($data['name'])->save();
                } else if(!empty($data['description'])) {
                    if(strlen($data['description']) < 200) throw new Exception(__('The description must be at least 200 characters'));
                    $this->getApplication()->setDescription($data['description'])->save();
                } else if(!empty($data['android_version'])) {
                    if(!preg_match("#^([0-9\.]+)$#", $data['android_version'])) {
                        throw new Exception(__('Invalid version'));
                    } else {
                        $this->getApplication()->getDevice(2)->setVersion($data['android_version'])->save();
                    }
                } else if(!empty($data['keywords'])) {
                    $this->getApplication()->setKeywords($data['keywords'])->save();
                } else if(!empty($data['bundle_id'])) {
                    if(count(explode('.', $data['bundle_id'])) < 2) {
                        throw new Exception(__('The entered bundle id is incorrect, it should be like: com.siberiancms.app'));
                    }
                    $this->getApplication()->setBundleId($data['bundle_id'])->save();
                }  else if(!empty($data['package_name'])) {
                    if(count(explode('.', $data['package_name'])) < 2) {
                        throw new Exception(__('The entered package name is incorrect, it should be like: com.siberiancms.app'));
                    }
                    $this->getApplication()->setPackageName($data['package_name'])->save();
                } else if(isset($data['main_category_id'])) {
                    if(empty($data['main_category_id'])) throw new Exception(__('The field is required'));
                    else $this->getApplication()->setMainCategoryId($data['main_category_id'])->save();
                } else if(isset($data['secondary_category_id'])) {
                    $this->getApplication()->setSecondaryCategoryId($data['secondary_category_id'])->save();
                } else if(isset($data['flag_use_ads'])) {
                    $this->getApplication()->setUseAds(isset($data['use_ads']))->save();
                } else if(isset($data['device_id'])) {
                    $device = $this->getApplication()->getDevice($data['device_id']);

                    if(isset($data["admob_id"])) {
                        $device->setAdmobId($data["admob_id"]);
                    } else if(isset($data["admob_interstitial_id"])) {
                        $device->setAdmobInterstitialId($data["admob_interstitial_id"]);
                    } else if(isset($data["admob_type"])) {
                        if($data["admob_type"] != "") {
                            $device->setAdmobType($data["admob_type"]);
                        } else {
                            throw new Exception(__('You must choose an ads type'));
                        }
                    }

                    $device->save();
                } else if(isset($data['ios_username'])) {
                    if(!empty($data['ios_username']) AND !Zend_Validate::is($data['ios_username'], "emailAddress")) throw new Exception(__('Please enter a valid email address'));
                    else $this->getApplication()->getDevice(1)
                        ->setUseOurDeveloperAccount(0)
                        ->setDeveloperAccountUsername(!empty($data['ios_username']) ? $data['ios_username'] : null)
                        ->save()
                    ;
                } else if(isset($data['ios_password'])) {
                    $this->getApplication()->getDevice(1)
                        ->setUseOurDeveloperAccount(0)
                        ->setDeveloperAccountPassword(!empty($data['ios_password']) ? $data['ios_password'] : null)
                        ->save()
                    ;
                } else if(isset($data['has_apple_account']) AND $data['has_apple_account'] == 2) {
                    $this->getApplication()->getDevice(1)
                        ->setDeveloperAccountUsername(null)
                        ->setDeveloperAccountPassword(null)
                        ->setUseOurDeveloperAccount(1)
                        ->save()
                    ;
                } else if(isset($data['android_username'])) {
                    if(!empty($data['android_username']) AND !Zend_Validate::is($data['android_username'], "emailAddress")) throw new Exception(__('Please enter a valid email address'));
                    else $this->getApplication()->getDevice(2)
                        ->setUseOurDeveloperAccount(0)
                        ->setDeveloperAccountUsername(!empty($data['android_username']) ? $data['android_username'] : null)
                        ->save()
                    ;
                } else if(isset($data['android_password'])) {
                    $this->getApplication()->getDevice(2)
                        ->setUseOurDeveloperAccount(0)
                        ->setDeveloperAccountPassword(!empty($data['android_password']) ? $data['android_password'] : null)
                        ->save()
                    ;
                } else if(isset($data['has_android_account']) AND $data['has_android_account'] == 2) {
                    $this->getApplication()->getDevice(2)
                        ->setDeveloperAccountUsername(null)
                        ->setDeveloperAccountPassword(null)
                        ->setUseOurDeveloperAccount(1)
                        ->save()
                    ;
                }

                $html = array('success' => '1');

            }
            catch(Exception $e) {
                $html = array(
                    'message' => $e->getMessage()
                );
            }

            $this->_sendHtml($html);

        }

    }

    public function downloadsourceAction() {

        if($data = $this->getRequest()->getParams()) {

            $application = $this->getApplication();

            if(!$application->subscriptionIsActive()) {
                throw new Exception("You have to purchase the application before downloading the mobile source code.");
            }


            if($design_code = $this->getRequest()->getParam("design_code")) {
                $application->setDesignCode($design_code);
            }

            $type = $this->getRequest()->getParam("type");
            $device = ($this->getRequest()->getParam("device_id") == 1) ? "ios" : "android";
            $noads = ($this->getRequest()->getParam("no_ads") == 1) ? "noads" : "";
            $pDesign = $this->getRequest()->getParam("design_code");
            $design_code = (!empty($pDesign)) ? $pDesign : "ionic";

            # ACL Apk user
            if($type == "apk" && !$this->getAdmin()->canGenerateApk()) {
                throw new Exception("You are not allowed to generate APK.");
            }

            if($type == "apk") {
                $queue = new Application_Model_ApkQueue();

                $queue->setAppId($application->getId());
                $queue->setName($application->getName());
                $application->getDevice(2)->setStatusId(3)->save();
            } else {
                $queue = new Application_Model_SourceQueue();

                $queue->setAppId($application->getId());
                $queue->setName($application->getName());
                $queue->setType($device.$noads);
                $queue->setDesignCode($design_code);
            }

            $queue->setHost($this->getRequest()->getHttpHost());
            $queue->setUserId($this->getSession()->getAdminId());
            $queue->setUserType("admin");
            $queue->save();

            /** Fallback for SAE, or disabled cron */
            $reload = false;
            if(!Cron_Model_Cron::is_active()) {
                $cron = new Cron_Model_Cron();
                $value = ($type == "apk") ? "apkgenerator" : "sources";
                $task = $cron->find($value, "command");
                Siberian_Cache::__clearLocks();
                $siberian_cron = new Siberian_Cron();
                $siberian_cron->execute($task);
                $reload = true;
            }

            $more["apk"] = Application_Model_ApkQueue::getPackages($application->getId());
            $more["zip"] = Application_Model_SourceQueue::getPackages($application->getId());
            $more["queued"] = Application_Model_Queue::getPosition($application->getId());

            $data = array(
                "success" => 1,
                "message" => __("Application successfully queued for generation."),
                "more" => $more,
                "reload" => $reload,
            );

        } else {
            $data = array(
                "error" => 1,
                "message" => __("Missing parameters for generation."),
            );
        }

        $this->_sendHtml($data);

    }

    public function cancelqueueAction() {
        if($data = $this->getRequest()->getParams()) {

            $application = $this->getApplication();
            $type = $this->getRequest()->getParam("type");
            $device = ($this->getRequest()->getParam("device_id") == 1) ? "ios" : "android";
            $noads = ($this->getRequest()->getParam("no_ads") == 1) ? "noads" : "";

            Application_Model_Queue::cancel($application->getId(), $type, $device.$noads);

            $more["apk"] = Application_Model_ApkQueue::getPackages($application->getId());
            $more["zip"] = Application_Model_SourceQueue::getPackages($application->getId());
            $more["queued"] = Application_Model_Queue::getPosition($application->getId());

            $data = array(
                "success" => 1,
                "message" => __("Generation cancelled."),
                "more" => $more,
            );

        } else {
            $data = array(
                "error" => 1,
                "message" => __("Missing parameters for cancellation."),
            );
        }

        $this->_sendHtml($data);
    }

    public function switchtoionicAction() {

        if($data = $this->getRequest()->isPost()) {

            try {

                $application = $this->getApplication();

                $application->setDesignCode(Application_Model_Application::DESIGN_CODE_IONIC);

                if($design_id = $application->getDesignId()) {

                    $design = new Template_Model_Design();
                    $design->find($design_id);

                    if($design->getId()) {
                        $application->setDesign($design);
                        Template_Model_Design::generateCss($application, false, false, true);
                    }

                }

                $application->save();

                $html = array('success' => '1');

            }
            catch(Exception $e) {
                $html = array(
                    'message' => $e->getMessage()
                );
            }

            $this->_sendHtml($html);

        }

    }

}
