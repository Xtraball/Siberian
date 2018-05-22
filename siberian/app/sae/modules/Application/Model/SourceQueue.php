<?php

class Application_Model_SourceQueue extends Core_Model_Default {

    const ARCHIVE_FOLDER = "/var/tmp/jobs/";

    public $_devices = array(
        "ios" => 1,
        "iosnoads" => 1,
        "android" => 2,
    );

    public function __construct($data = array()) {
        parent::__construct($data);
        $this->_db_table = "Application_Model_Db_Table_SourceQueue";
    }

    /**
     * @param $status
     * @return mixed
     */
    public function changeStatus($status) {
        switch($status) {
            case "building":
                $this->setBuildTime(time());
                $this->setBuildStartTime(time());
                break;
            case "success":
                $this->setBuildTime(time() - $this->getBuildTime());
                break;
            default:
                $this->setBuildTime(0);
        }

        return $this->setStatus($status)->save();
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function generate() {
        $application = new Application_Model_Application();
        $application = $application->find($this->getAppId());

        if(!$application->getId()) {
            throw new Exception(__("#500-02: This application does not exist"));
        }

        # Refresh PEM
        if ($this->getIsRefreshPem()) {
            $this->sendPemToAutoPublishServer($application);
            return true;
        }

        $design_code = (in_array($this->getDesignCode(), array("angular", "ionic"))) ? $this->getDesignCode() : "ionic";

        $application->setDesignCode($design_code);
        $device = $application->getDevice($this->_devices[$this->getType()]);
        $device->setApplication($application);
        $device->setExcludeAds(($this->getType()=="iosnoads"));
        $device->setDownloadType("zip");
        $device->setHost($this->getHost());

        // FAST
        $result = $device->getResources();

        $recipients = array();
        switch($this->getUserType()) {
            case "backoffice":
                $backoffice = new Backoffice_Model_User();
                $backoffice_user = $backoffice->find($this->getUserId());
                if($backoffice_user->getId()) {
                    $recipients[] = $backoffice_user;
                }
                break;
            case "admin":
                $admin = new Admin_Model_Admin();
                $admin_user = $admin->find($this->getUserId());
                if($admin_user->getId()) {
                    $recipients[] = $admin_user;
                }
                break;
        }


        $type = ($this->getType() == "android") ? __("Android Source") : __("iOS Source");
        if(file_exists($result)) {
            $this->changeStatus("success");
            $this->setPath($result);
            $this->save();

            /** Success email */
            $protocol = (System_Model_Config::getValueFor("use_https")) ? "https://" : "http://";
            $url = $protocol.$this->getHost()."/".str_replace(Core_Model_Directory::getBasePathTo(""), "", $result);

            $values = array(
                "type" => $type,
                "application_name" => $this->getName(),
                "link" => $url,
            );

            # @version 4.8.7 - SMTP
            $mail = new Siberian_Mail();
            $mail->simpleEmail("queue", "source_queue_success", __("%s generation success for App: %s", $type, $application->getName()), $recipients, $values);
            $mail->send();

        } else {
            $this->changeStatus("failed");
            $this->save();

            /** Failed email */
            $values = array(
                "type" => $type,
                "application_name" => $this->getName(),
            );

            # @version 4.8.7 - SMTP
            $mail = new Siberian_Mail();
            $mail->simpleEmail("queue", "source_queue_failed", __("The requested %s generation failed: %s", $type, $application->getName()), $recipients, $values);
            $mail->send();
        }

        if ($this->getIsAutopublish()) {
            $this->sendJobToAutoPublishServer($application, $result);
        }

        return $result;
    }

    /**
     * @param $application
     * @param $sourcePath
     * @throws Exception
     * @throws Zend_Json_Exception
     */
    protected function sendJobToAutoPublishServer($application, $sourcePath)
    {
        $appId = $application->getId();

        // iOS license key!
        $licenseKey = __get('ios_autobuild_key');

        // Application infos!
        $app = (new Application_Model_Application())
            ->find($appId);

        // Backoffice user!
        $user = (new Backoffice_Model_User())
            ->find($this->getUserId());
        $usermail = $user->getEmail();

        // iOS setting info
        $appIosAutopublish = (new Application_Model_IosAutopublish())
            ->find($appId, 'app_id');

        $languages = Siberian_Json::decode($appIosAutopublish->getLanguages());

        if (!array_key_exists('error', $languages)) {
            if (count($languages) === 0) {
                throw new Siberian_Exception('There is no language selected');
            }
        } else {
            throw new Siberian_Exception('Cannot unserialize language data');
        }

        //we keep using ISO-639 for siberian storage but we have to translate ISO code to faslane languages name
        $fastlane_languages = [];
        foreach ($languages as $codeIos => $activated) {
            $fast_label = Application_Model_Languages::getLabelFromCodeIso($codeIos);
            $fastlane_languages[$fast_label] = $activated;
        }

        $data = [
            'name' => $app->getName(),
            'bundle_id' => $app->getBundleId(),
            'want_to_autopublish' => $appIosAutopublish->getWantToAutopublish(),
            'credentials' => $appIosAutopublish->getCypheredCredentials(),
            'team_id' => $appIosAutopublish->getTeamId(),
            'team_name' => $appIosAutopublish->getTeamName(),
            'itc_provider' => $appIosAutopublish->getItcProvider(),
            'has_bg_locate' => $appIosAutopublish->getHasBgLocate(),
            'has_audio' => $appIosAutopublish->getHasAudio(),
            'languages' => $fastlane_languages,
            'host' => $this->getHost(),
            'license_key' => $licenseKey,
            'token' => $appIosAutopublish->getToken(),
            'email' => $usermail,
        ];

        $jobCode = time() . '-' . $appIosAutopublish->getToken();
        $jobFolder = Core_Model_Directory::getBasePathTo(self::ARCHIVE_FOLDER . $jobCode);

        if (!mkdir($jobFolder,0777,true)) {
            throw new Siberian_Exception('Cannot create folder ' . $jobFolder);
        }

        if (!copy($sourcePath, $jobFolder . '/sources.zip')) {
            throw new Siberian_Exception('Cannot copy sources to job folder');
        }

        $configJobFilePath = $jobFolder . '/config.json';
        $json = Siberian_Json::encode($data, JSON_PRETTY_PRINT);

        if (!array_key_exists('error', $json)) {
            file_put_contents($configJobFilePath, $json);
        } else {
            throw new Siberian_Exception('Cannot create json config job file');
        }

        $tgzJobFilePath = $jobFolder . '.tgz';

        exec("tar zcf $tgzJobFilePath -C $jobFolder sources.zip config.json", $output, $return_val);

        if ($return_val !== 0) {
            throw new Siberian_Exception('Cannot create zip job file');
        }

        $jobUrlEncoded = base64_encode('http://'.$this->getHost().'/var/tmp/jobs/'.$jobCode.'.tgz');

        Siberian_Request::get(
            "https://jenkins-prod02.xtraball.com/job/ios-autopublish/buildWithParameters",
            [
                'token' => 'O0cRwnWPjcfMmXc89SQ3RbVRPGXLQF6a',
                'SIBERIAN_JOB_URL' => $jobUrlEncoded,
            ],
            null,
            [
                'type' => 'basic',
                'username' => 'ios-builder',
                'password' => 'ced2eb561db43afb09c633b8f68c1f17',
            ]);

        if (Siberian_Request::$statusCode != 200) {
            throw new Siberian_Exception(__('Cannot send build to service %s.', Siberian_Request::$statusCode));
        }
    }

    /**
     * @param $application
     * @throws Siberian_Exception
     * @throws Zend_Exception
     */
    protected function sendPemToAutoPublishServer($application)
    {
        $appId = $application->getId();

        // iOS license key!
        $licenseKey = __get('ios_autobuild_key');

        // Application infos!
        $app = (new Application_Model_Application())
            ->find($appId);

        // Backoffice user!
        $user = (new Backoffice_Model_User())
            ->find($this->getUserId());
        $usermail = $user->getEmail();

        // iOS setting info
        $appIosAutopublish = (new Application_Model_IosAutopublish())
            ->find($appId, 'app_id');

        $languages = Siberian_Json::decode($appIosAutopublish->getLanguages());

        if (!array_key_exists('error', $languages)) {
            if (count($languages) === 0) {
                throw new Siberian_Exception('There is no language selected');
            }
        } else {
            throw new Siberian_Exception('Cannot unserialize language data');
        }

        //we keep using ISO-639 for siberian storage but we have to translate ISO code to faslane languages name
        $fastlane_languages = [];
        foreach ($languages as $codeIos => $activated) {
            $fast_label = Application_Model_Languages::getLabelFromCodeIso($codeIos);
            $fastlane_languages[$fast_label] = $activated;
        }

        $data = [
            'name' => $app->getName(),
            'bundle_id' => $app->getBundleId(),
            'want_to_autopublish' => $appIosAutopublish->getWantToAutopublish(),
            'credentials' => $appIosAutopublish->getCypheredCredentials(),
            'team_id' => $appIosAutopublish->getTeamId(),
            'team_name' => $appIosAutopublish->getTeamName(),
            'itc_provider' => $appIosAutopublish->getItcProvider(),
            'has_bg_locate' => $appIosAutopublish->getHasBgLocate(),
            'has_audio' => $appIosAutopublish->getHasAudio(),
            'languages' => $fastlane_languages,
            'host' => $this->getHost(),
            'license_key' => $licenseKey,
            'token' => $appIosAutopublish->getToken(),
            'email' => $usermail,
            'refresh_pem' => true,
        ];

        $jobCode = time() . '-' . $appIosAutopublish->getToken();
        $jobFolder = Core_Model_Directory::getBasePathTo(self::ARCHIVE_FOLDER . $jobCode);

        if (!mkdir($jobFolder,0777,true)) {
            throw new Siberian_Exception('Cannot create folder ' . $jobFolder);
        }

        $fakeSources = Core_Model_Directory::getBasePathTo('/var/apps/ionic/refresh_pem.zip');
        if (!copy($fakeSources, $jobFolder . '/sources.zip')) {
            throw new Siberian_Exception('Cannot copy sources to job folder');
        }

        $configJobFilePath = $jobFolder . '/config.json';
        $json = Siberian_Json::encode($data, JSON_PRETTY_PRINT);

        if (!array_key_exists('error', $json)) {
            file_put_contents($configJobFilePath, $json);
        } else {
            throw new Siberian_Exception('Cannot create json config job file');
        }

        $tgzJobFilePath = $jobFolder . '.tgz';

        exec("tar zcf $tgzJobFilePath -C $jobFolder sources.zip config.json", $output, $return_val);

        if ($return_val !== 0) {
            throw new Siberian_Exception('Cannot create zip job file');
        }

        $jobUrlEncoded = base64_encode('http://'.$this->getHost().'/var/tmp/jobs/'.$jobCode.'.tgz');

        Siberian_Request::get(
            "https://jenkins-prod02.xtraball.com/job/generate-pem/buildWithParameters",
            [
                'token' => '6EJQwGkCLzTTvSWUfY19a3QshNvk8RXK',
                'SIBERIAN_JOB_URL' => $jobUrlEncoded,
            ],
            null,
            [
                'type' => 'basic',
                'username' => 'ios-builder',
                'password' => 'ced2eb561db43afb09c633b8f68c1f17',
            ]);

        if (Siberian_Request::$statusCode != 200) {
            throw new Siberian_Exception(__('Cannot send build to service %s.', Siberian_Request::$statusCode));
        }
    }

    /**
     * Fetch if some apps are building.
     *
     * @param $application_id
     * @return array
     */
    public static function getStatus($application_id) {
        $table = new self();
        $results = $table->findAll(array(
            "app_id" => $application_id,
            "status IN (?)" => array("queued", "building"),
        ));

        $data = array(
            "ios" => false,
            "iosnoads" => false,
            "android" => false,
        );
        
        foreach($results as $result) {
            $type = $result->getType();
            if(array_key_exists($type, $data)) {
                # Set is building
                $data[$type] = true;
            }
        }

        return $data;
    }

    /**
     * Fetch if some apps are done.
     *
     * @param $applicationId
     * @return array
     */
    public static function getPackages($applicationId)
    {
        $table = new self();
        $results = $table->findAll(
            [
                'app_id' => $applicationId,
                'status IN (?)' => ['success'],
            ],
            [
                'updated_at DESC'
            ]
        );

        $basePath = Core_Model_Directory::getBasePathTo('');
        $data = [];

        foreach ($results as $result) {
            $type = $result->getType();
            if (!array_key_exists($type, $data)) {
                // Test if the path still exists!
                if (is_file($result->getData('path'))) {
                    // Fetch the path
                    $data[$type] = [
                        'path' => str_replace($basePath, '/', $result->getData('path')),
                        'date' => datetime_to_format($result->getUpdatedAt())
                    ];
                } else {
                    $result
                        ->setData('path', '')
                        ->save();
                }
            }
        }

        return $data;
    }

    /**
     * @return Application_Model_SourceQueue[]
     */
    public static function getQueue() {
        $table = new self();
        $results = $table->findAll(
            array("status IN (?)" => array("queued")),
            array("created_at ASC")
        );

        return $results;
    }

    /**
     * Fetch builds started from more than 1 hour
     *
     * @param $current_time
     * @return Push_Model_Message[]
     */
    public static function getStuck($current_time) {
        $table = new self();

        $results = $table->findAll(
            array(
                "status = ?" => "building",
                "(build_start_time + 3600) < ?" => $current_time
            ),
            array("created_at ASC")
        );

        return $results;
    }

    /**
     * Clear old pathts when clearing tmp cache in backoffice
     */
    public static function clearPaths() {
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->query("UPDATE source_queue SET path = '' WHERE status != 'building';");
    }
    
}
