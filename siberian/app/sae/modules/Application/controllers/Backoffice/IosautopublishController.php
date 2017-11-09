<?php
class Application_Backoffice_IosautopublishController extends Backoffice_Controller_Default
{

    public function saveinfoiosautopublishAction() {

        if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {
            try {
                if(empty($data["app_id"])) {
                    throw new Exception(__("An error occurred while saving. Please try again later."));
                }

                if(strlen($data['infos']["itunes_login"]) == 0 || strlen($data['infos']["itunes_password"]) == 0) {
                    throw new Exception(__("Please fill iTunes Connect Credentials."));
                }

                if(empty($data['infos']["languages"])) {
                    throw new Exception(__("Please select at least one language."));
                }

                $application = new Application_Model_Application();
                $application->find($data["app_id"]);

                if(!$application->getId()) {
                    throw new Exception(__("An error occurred while saving. Please try again later."));
                }

                $appIosAutopublish = new Application_Model_IosAutopublish();
                $appIosAutopublish->find($data["app_id"],"app_id");

                $appIosAutopublish
                    ->setAppId($data["app_id"])
                    ->setWantToAutopublish(1)
                    // ->setWantToAutopublish($data['infos']["want_to_autopublish"])
                    ->setItunesLogin($data['infos']["itunes_login"])
                    ->setItunesPassword($data['infos']["itunes_password"])
                    ->setHasAds($data['infos']["has_ads"])
                    ->setHasBgLocate($data['infos']["has_bg_locate"])
                    ->setHasAudio($data['infos']["has_audio"])
                    ->setLanguages(Zend_Json::encode(array($data['infos']["languages"] => true)));

                if(!$appIosAutopublish->getToken()) {
                    $appIosAutopublish->setToken(md5(
                        $data['infos']["itunes_login"].
                        $data['infos']["itunes_password"].
                        $data["app_id"].
                        time().
                        "saltystring!"
                    ));
                }

                $appIosAutopublish->save();

                $data = array(
                    "success"   => 1,
                    "message"   => __("Info successfully saved")
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

    public function generateiosautopublishAction() {
        if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {
            try {
                if(empty($data["app_id"]) ) {
                    throw new Exception(__("An error occurred while generating. Please try again later."));
                }

                $application = new Application_Model_Application();
                $application->find($data["app_id"]);

                if(!$application->getId()) {
                    throw new Exception(__("An error occurred while generating. Please try again later."));
                }

                $appIosAutopublish = new Application_Model_IosAutopublish();
                $appIosAutopublish->find($data["app_id"],"app_id");

                $noads = ($appIosAutopublish->getHasAds() == 1) ? "" : "noads";

                $design_code = $application->getData("design_code");

                $queue = new Application_Model_SourceQueue();

                $queue->setAppId($data["app_id"]);
                $queue->setName($application->getName());
                $queue->setType("ios".$noads);
                $queue->setDesignCode($design_code);
                $queue->setIsAutopublish("1");

                $queue->setHost($this->getRequest()->getHttpHost());
                $queue->setUserId($this->getSession()->getBackofficeUserId());
                $queue->save();

                $more["zip"] = Application_Model_SourceQueue::getPackages($application_id);
                $more["queued"] = Application_Model_Queue::getPosition($application_id);

                $data = array(
                    "success"   => 1,
                    "message"   => __("Generation successfully queued."),
                    "more" => $more,
                );

                $appIosAutopublish->setData("last_build_status","pending");
                $appIosAutopublish->save();

            } catch(Exception $e) {
                $data = array(
                    "error" => 1,
                    "message" => $e->getMessage()
                );
            }
        } else {
            $data = array(
                "error" => 1,
                "message" => __("Cannot get parameters.")
            );
        }

        $this->_sendHtml($data);
    }


    public function updatejobstatusAction() {

        try {

            $token = $this->getRequest()->getParam("token",null);
            $status = $this->getRequest()->getParam("status",null);
            $error_message = $this->getRequest()->getParam("error_message",null);
            $last_builded_ipa_link = $this->getRequest()->getParam("last_builded_ipa_link",null);


            if(is_null($token) || is_null($status)) {
                throw new Exception("Wrong params.");
            }

            if(!in_array($status, array("pending","queued","building","success","failed"))) {
                throw new Exception("Wrong params.");
            }

            $appIosAutopublish = new Application_Model_IosAutopublish();
            $appIosAutopublish->find($token,"token");

            if(!is_numeric($appIosAutopublish->getId())) {
                throw new Exception("Wrong params.");
            }

            switch ($status) {
                case 'success':
                    $appIosAutopublish->setData("last_success",time());
                    $appIosAutopublish->setData("last_finish",time());

                    $application = new Application_Model_Application();
                    $application->find($appIosAutopublish->getId());
                    if(!$application->getId()) {
                        throw new Exception("Cannot get application from token.");
                    }
                    //1 is iOS
                    $device = $application->getDevice(1);
                    $device->setData("status_id",3)->save();
                    break;
                case 'failed':
                    $appIosAutopublish->setData("last_finish",time());
                    break;
            }

            if(!is_null($last_builded_ipa_link)) {
                $appIosAutopublish->setData("last_builded_ipa_link",$last_builded_ipa_link);
            }

            if(!is_null($error_message)) {
                $appIosAutopublish->setData("error_message",base64_decode($error_message));
            }

            $appIosAutopublish->setData("last_build_status",$status);
            $appIosAutopublish->save("last_build_status");

            $data = array(
                "success" => 1,
                "message" => __("OK")
            );

        } catch (Exception $e) {
            // print_r($e->getTrace());
            // die;
            //we limit attack by returning same error in all error case
            $data = array(
                "error" => 1,
                "message" => __($e->getMessage())
            );
        }

        $this->_sendHtml($data);
    }

    public function uploadcertificateAction() {
        $token = $this->getRequest()->getParam("token",null);

        if(is_null($token)) {
            throw new Exception("Wrong params.");
        }

        if (empty($_FILES) || empty($_FILES['file']['name'])) {
            throw new Exception("Wrong params.");
        }

        $appIosAutopublish = new Application_Model_IosAutopublish();
        $appIosAutopublish->find($token,"token");

        $appId = $appIosAutopublish->getData("app_id");

        $application = new Application_Model_Application();
        $application->find($appId);

        if(!$application->getId()) {
            throw new Exception("Wrong params.");
        }

        $base_path = Core_Model_Directory::getBasePathTo("var/apps/iphone/");
        if(!is_dir($base_path)) mkdir($base_path, 0775, true);
        $path = Core_Model_Directory::getPathTo("var/apps/iphone/");
        $adapter = new Zend_File_Transfer_Adapter_Http();
        $adapter->setDestination(Core_Model_Directory::getTmpDirectory(true));

        if ($adapter->receive()) {

            $file = $adapter->getFileInfo();

            $certificat = new Push_Model_Certificate();
            $certificat->find(array('type' => 'ios', 'app_id' => $appId));

            if(!$certificat->getId()) {
                $certificat->setType("ios")
                ->setAppId($appId);
            }

            $new_name = uniqid("cert_").".pem";
            if(!rename($file["file"]["tmp_name"], $base_path.$new_name)) {
                throw new Exception("Wrong params.");
            }

            $certificat->setPath($path.$new_name)->save();

            $data = array(
                "success" => 1,
                "pem_infos" => Push_Model_Certificate::getInfos($appId),
                "message" => __("The file has been successfully uploaded")
            );

        } else {
            throw new Exception("Wrong params.");
        }

        $this->_sendHtml($data);

    }

}
