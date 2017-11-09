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

        if($this->getIsAutopublish()) {
            $this->sendJobToAutoPublishServer($application, $result);
        }

        return $result;
    }

    protected function sendJobToAutoPublishServer($application, $sourcePath) {
        $app_id = $application->getId();

        //ios license key
        $config = new System_Model_Config();
        $config->find("ios_autobuild_key","code");
        $license_key = $config->getValue();

        //application infos
        $app = new Application_Model_Application();
        $app->find($app_id);

        //backoffice user
        $user = new Backoffice_Model_User();
        $user->find($this->getUserId());
        $usermail = $user->getEmail();

        //ios setting info
        $appIosAutopublish = new Application_Model_IosAutopublish();
        $appIosAutopublish->find($app_id, "app_id");

        if($languages = Zend_Json::decode($appIosAutopublish->getLanguages())) {
            if(count($languages) === 0) {
                throw new Exception("There is no language selected");
            }
        } else {
            throw new Exception("Cannot unserialize language data");
        }

        //we keep using ISO-639 for siberian storage but we have to translate ISO code to faslane languages name
        $fastlane_languages = array();
        foreach ($languages as $codeIos => $activated) {
            $fast_label = Application_Model_Languages::getLabelFromCodeIso($codeIos);
            $fastlane_languages[$fast_label] = $activated;
        }

        $data = array(
            "name" => $app->getName(),
            "bundle_id" => $app->getBundleId(),
            "want_to_autopublish" => $appIosAutopublish->getWantToAutopublish(),
            "itunes_login" => $appIosAutopublish->getItunesLogin(),
            "itunes_password" => $appIosAutopublish->getItunesPassword(),
            "has_bg_locate" => $appIosAutopublish->getHasBgLocate(),
            "has_audio" => $appIosAutopublish->getHasAudio(),
            "languages" => $fastlane_languages,
            "host" => $this->getHost(),
            "license_key" => $license_key,
            "token" => $appIosAutopublish->getToken(),
            "email" => $usermail,
        );

        $jobCode = time().'-'.$appIosAutopublish->getToken();
        $jobFolder = Core_Model_Directory::getBasePathTo(self::ARCHIVE_FOLDER.$jobCode);

        if(!mkdir($jobFolder,0777,true)) {
            throw new Exception("Cannot create folder $jobFolder");
        }

        if(!copy($sourcePath, $jobFolder."/sources.zip")) {
            throw new Exception("Cannot copy sources to job folder");
        }

        $configJobFilePath = $jobFolder."/config.json";

        if($json = Zend_Json::encode($data)) {
            file_put_contents($configJobFilePath, $json);
        } else {
            throw new Exception("Cannot create json config job file");
        }

        $tgzJobFilePath = $jobFolder . '.tgz';

        exec("tar zcf $tgzJobFilePath -C $jobFolder sources.zip config.json", $output, $return_val);

        if($return_val !== 0) {
            throw new Exception("Cannot create zip job file");
        }

        $jobUrlEncoded = base64_encode('http://'.$this->getHost().'/var/tmp/jobs/'.$jobCode.'.tgz');

        $request = curl_init();
        # Setting options
        curl_setopt($request, CURLOPT_URL, "http://jenkins.xtraball.com/job/ios-autopublish/buildWithParameters?token=2a66b48d4a926a23ee92195d73251c22&SIBERIAN_JOB_URL=$jobUrlEncoded");
        curl_setopt($request, CURLOPT_TIMEOUT, 3);
        curl_setopt($request, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($request, CURLOPT_USERPWD, "ios-builder:ced2eb561db43afb09c633b8f68c1f17");
        # Call
        curl_exec($request);
        # Closing connection

        if(curl_errno($request) > 0) {
            throw new Exception("Cannot start autopublish job process");
        }

        curl_close($request);

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
     * @param $application_id
     * @return array
     */
    public static function getPackages($application_id) {
        $table = new self();
        $results = $table->findAll(array(
            "app_id" => $application_id,
            "status IN (?)" => array("success"),
        ), array("updated_at DESC"));

        $base_path = Core_Model_Directory::getBasePathTo("");
        $data = array();

        foreach($results as $result) {
            $type = $result->getType();
            if(!array_key_exists($type, $data)) {
                # Set is building
                $data[$type] = array(
                    "path" => str_replace($base_path, "", $result->getData("path")), /** Frakking conflict */
                    "date" => datetime_to_format($result->getUpdatedAt())
                );
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
