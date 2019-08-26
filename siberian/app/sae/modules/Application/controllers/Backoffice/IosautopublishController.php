<?php

use Siberian\Json;

/**
 * Class Application_Backoffice_IosautopublishController
 */
class Application_Backoffice_IosautopublishController extends Backoffice_Controller_Default
{
    /**
     * Check iTunes credentials validity and returns teams on success!
     *
     * - On success also saves cypheredCredentials on database!
     * - On failure doesn't save credentials!
     */
    public function savecredentialsAction ()
    {
        try {
            $request = $this->getRequest();
            $params = $request->getBodyParams();
            $ios = $params["login"];

            if (empty($params)) {
                throw new \Siberian\Exception("#325-01: " . __("Missing parameters."));
            }

            if (empty($params["app_id"])) {
                throw new \Siberian\Exception("#325-02: " . __("App Id is required!"));
            }

            if ((empty($ios["itunes_login"]) || empty($ios["itunes_password"]))) {
                throw new \Siberian\Exception("#325-03: " . __("Please fill App Store Connect Credentials."));
            }

            if (empty($ios["selected_team"]) || empty($ios["selected_team_name"]) || empty($ios["selected_provider"])) {
                throw new \Siberian\Exception("#325-04: " . __("Please fill Dev team & Provider."));
            }

            $stats = null;

            $appIosAutopublish = (new Application_Model_IosAutopublish())
                ->find($params["app_id"], "app_id");

            if ($ios["itunes_password"] != Application_Model_IosAutopublish::$fakePassword) {
                // Save password only if different from fake!
                $cypheredCredentials = Application_Model_IosAutopublish::cypher(
                    $ios["itunes_login"] . ":" . $ios["itunes_password"]);

                $appIosAutopublish
                    ->setCypheredCredentials($cypheredCredentials)
                    ->setItunesLogin($ios["itunes_login"]);
            }

            $appIosAutopublish
                ->setAppId($params["app_id"])
                ->setAccountType("non2fa") // Now enforced non-2fa accounts!
                ->setTeams(Json::encode([]))
                ->setItunesLogin($ios["itunes_login"])
                ->setItunesPassword("") // Clear old "clear" login
                ->setItunesOriginalLogin($ios["itunes_original_login"])
                ->setTeamId($ios["selected_team"])
                ->setTeamName($ios["selected_team_name"])
                ->setItcProvider($ios["selected_provider"])
                ->setHasAds($ios["has_ads"])
                ->setHasAudio($ios["has_audio"])
                ->save();

            $stats = $appIosAutopublish->getStats();

            $payload = [
                "success" => true,
                "message" => __("Credentials successfully saved!"),
                "id" => $appIosAutopublish->getId(),
                "stats" => $stats
            ];

        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * saving ios autopublish settings!
     */
    public function requestpublicationAction()
    {
        try {
            $request = $this->getRequest();
            $params = Siberian_Json::decode($request->getRawBody());

            if (empty($params)) {
                throw new \Siberian\Exception("#330-01: " . __("Missing parameters."));
            }

            if (empty($params["app_id"])) {
                throw new \Siberian\Exception("#330-02: " . __("App Id is required!"));
            }

            $appIosAutopublish = (new Application_Model_IosAutopublish())
                ->find($params["app_id"],"app_id");

            if (!$appIosAutopublish->getId()) {
                throw new \Siberian\Exception("#330-03: " . __("No credentials found!"));
            }

            if (empty($params["infos"]["languages"])) {
                throw new \Siberian\Exception("#330-04: " . __("Please select at least one language."));
            }

            $application = (new Application_Model_Application())
                ->find($params["app_id"]);

            if (!$application->getId()) {
                throw new \Siberian\Exception("#330-05: " . __("Application not found!"));
            }

            // Find selected team!
            if (empty($appIosAutopublish->getTeamId()) ||
                empty($appIosAutopublish->getItcProvider())) {
                throw new \Siberian\Exception("#330-06: " .
                    __("You must select both a Development Team & a Provider!"));
            }


            $appIosAutopublish
                ->setAppId($params["app_id"])
                ->setWantToAutopublish(1)
                ->setLanguages(Siberian_Json::encode([
                    $params["infos"]["languages"] => true
                ]));

            // Salting token!
            if (!$appIosAutopublish->getToken()) {
                $string = sprintf("%s%s%s%s",
                    $params["app_id"],
                    time(),
                    $appIosAutopublish->getCypheredCredentials(),
                    "mySaltIsTasty!");

                $appIosAutopublish->setToken(sha1($string));
            }

            $appIosAutopublish->save();

            // Build phase!
            $noads = ($appIosAutopublish->getHasAds() == 1) ? "" : "noads";

            $queue = new Application_Model_SourceQueue();
            $queue
                ->setAppId($application->getId())
                ->setName($application->getName())
                ->setType("ios" . $noads)
                ->setDesignCode("ionic")
                ->setIsAutopublish("1")
                ->setHost($request->getHttpHost())
                ->setUserId($this->getSession()->getBackofficeUserId())
                ->setUserType("backoffice")
                ->save();

            $more["zip"] = Application_Model_SourceQueue::getPackages($application->getId());
            $more["queued"] = Application_Model_Queue::getPosition($application->getId());

            $appIosAutopublish->setData("last_build_status", "pending");
            $appIosAutopublish->save();
#
            $payload = [
                "success" => true,
                "message" => __("Generation successfully queued."),
                "more" => $more
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * Upload APK from jenkins
     *
     * @throws Zend_Layout_Exception
     */
    public function uploadapkAction ()
    {
        $request = $this->getRequest();
        try {
            $appId = $request->getParam('appId', false);
            if ($appId === false) {
                throw new \Siberian\Exception('#565-04: ' . __('Missing appId'));
            }

            if (empty($_FILES) || empty($_FILES['file']['name'])) {
                throw new \Siberian\Exception('#565-02: ' . __('No file has been sent'));
            }

            $basePath = Core_Model_Directory::getBasePathTo("var/tmp/applications/ionic/");
            if (!is_dir($basePath)) {
                mkdir($basePath, 0775, true);
            }
            $adapter = new Zend_File_Transfer_Adapter_Http();
            $adapter->setDestination(Core_Model_Directory::getTmpDirectory(true));

            $queue = Application_Model_SourceQueue::getApkServiceStatus($appId);

            if ($adapter->receive()) {
                $file = $adapter->getFileInfo();

                $destination = $basePath . $file['file_0_']['name'];
                exec("rm -f '{$destination}'");
                if (!rename($file['file_0_']['tmp_name'], $destination)) {
                    throw new \Siberian\Exception(
                        '#565-01: ' .
                        __("An error occurred while saving your APK. Please try again later."));
                }

                if (isset($file['file_1_'])) {
                    // We have a new keystore
                    $destinationKeystore = Core_Model_Directory::getBasePathTo(
                        '/var/apps/android/keystore/' . $appId . '.pks');

                    // If there is already a pks, we will rename/backup it!
                    if (is_file($destinationKeystore)) {
                        rename($destinationKeystore, str_replace(
                            '.pks',
                            '-backup-' . date('Y-m-d_H-i-s') .  '.pks',
                            $destinationKeystore));
                    }

                    if (!rename($file['file_1_']['tmp_name'], $destinationKeystore)) {
                        throw new \Siberian\Exception(
                            '#565-05: ' .
                            __("An error occurred while saving your Keystore. Please try again later."));
                    }
                }

                $serviceQueue = Application_Model_SourceQueue::getApkServiceQueue($appId);
                $serviceQueue
                    ->setApkPath($destination)
                    ->setStatus('success')
                    ->setApkStatus('success')
                    ->save();

                $this->apkServiceEmail($serviceQueue);

                $payload = [
                    'success' => 1
                ];

            } else {
                $messages = $adapter->getMessages();
                if (!empty($messages)) {
                    $message = implode("\n", $messages);
                } else {
                    $message = '#565-03: ' . __("An error occurred while upload the APK. Please try again later.");
                }

                throw new \Siberian\Exception($message);
            }
        } catch (\Exception $e) {
            $serviceQueue = Application_Model_SourceQueue::getApkServiceQueue($appId);
            $serviceQueue
                ->setStatus('failed')
                ->setApkStatus('failed')
                ->setApkMessage($e->getMessage())
                ->save();

            $this->apkServiceEmail($serviceQueue);

            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function apkservicestatusAction ()
    {
        $request = $this->getRequest();
        try {
            $appId = $request->getParam('appId', false);
            if ($appId === false) {
                throw new \Siberian\Exception('#566-01: ' . __('Missing appId'));
            }

            $status = $request->getParam('status', false);
            if ($status === false) {
                throw new \Siberian\Exception('#566-02: ' . __('Missing status'));
            }

            $message = $request->getParam('message', false);

            $serviceQueue = Application_Model_SourceQueue::getApkServiceQueue($appId);
            $serviceQueue
                ->setStatus($status)
                ->setApkStatus($status)
                ->setApkMessage($message)
                ->save();

            $this->apkServiceEmail($serviceQueue);

        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * @param Application_Model_SourceQueue $queue
     * @throws Zend_Layout_Exception
     */
    private function apkServiceEmail ($queue)
    {
        try {
            $recipients = [];
            switch ($queue->getUserType()) {
                case 'backoffice':
                    $backofficeUser = (new Backoffice_Model_User())
                        ->find($queue->getUserId());
                    if ($backofficeUser->getId()) {
                        $recipients[] = $backofficeUser;
                    }
                    break;
                case 'admin':
                    $adminUser = (new Admin_Model_Admin())
                        ->find($queue->getUserId());
                    if($adminUser->getId()) {
                        $recipients[] = $adminUser;
                    }
                    break;
            }

            $protocol = "https";
            if ($queue->getApkStatus() === 'success') {
                // Success email!
                $url = sprintf("%s://%s/%s",
                    $protocol,
                    $queue->getHost(),
                    str_replace(Core_Model_Directory::getBasePathTo(''), '', $queue->getApkPath())
                );

                $values = [
                    'type' => __("APK Generator Service"),
                    'application_name' => $queue->getName(),
                    'link' => $url,
                ];

                // SMTP Mailer
                (new Siberian_Mail())
                    ->simpleEmail(
                        'queue',
                        'apk_queue_success',
                        __("APK generation for App: %s", $queue->getName()),
                        $recipients,
                        $values)
                    ->send();

            } else {
                // Failure email!
                $values = [
                    'type' => __('APK Generator Service'),
                    'application_name' => $queue->getName(),
                ];

                // SMTP Mailer
                (new Siberian_Mail())
                    ->simpleEmail(
                        'queue',
                        'apk_queue_failed',
                        __("The requested APK generation failed: %s", $queue->getName()),
                        $recipients,
                        $values)
                    ->send();
            }
        } catch (\Exception $e) {
            // Success / Failure mail was not ok, silent error to continue!
        }
    }

    public function updatejobstatusAction() {

        try {
            $request = $this->getRequest();

            $token = $request->getParam('token',null);
            $status = $request->getParam('status',null);
            $error_message = $request->getParam('error_message',null);
            $last_builded_ipa_link = $request->getParam('last_builded_ipa_link',null);


            if (is_null($token) || is_null($status)) {
                throw new \Siberian\Exception(__('Missing token and/or status.'));
            }

            $availableStatuses = [
                'pending',
                'queued',
                'building',
                'success',
                'failed',
            ];
            if (!in_array($status, $availableStatuses)) {
                throw new \Siberian\Exception(__('Invalid status `%s`.', $status));
            }

            $appIosAutopublish = (new Application_Model_IosAutopublish())
                ->find($token,'token');

            if (!$appIosAutopublish->getId()) {
                throw new \Siberian\Exception(__('Unable to find the corresponding build.'));
            }

            switch ($status) {
                case 'success':
                    $appIosAutopublish->setData('last_success', time());
                    $appIosAutopublish->setData('last_finish', time());

                    $application = (new Application_Model_Application())
                        ->find($appIosAutopublish->getId());
                    if (!$application->getId()) {
                        throw new \Siberian\Exception(__('Cannot find application from token.'));
                    }
                    //1 is iOS
                    $device = $application->getDevice(1);
                    $device->setData('status_id', 3)->save();
                    break;
                case 'failed':
                    $appIosAutopublish->setData('last_finish', time());
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

        $this->_sendJson($data);

    }

}
