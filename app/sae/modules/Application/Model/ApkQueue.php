<?php

class Application_Model_ApkQueue extends Core_Model_Default {

    public function __construct($data = array()) {
        parent::__construct($data);
        $this->_db_table = "Application_Model_Db_Table_ApkQueue";
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
     * Generate APK in cron queue
     *
     * @throws Exception
     */
    public function generate() {
        $application = new Application_Model_Application();
        $application = $application->find($this->getAppId());

        if(!$application->getId()) {
            throw new Exception(__("#500-01: This application does not exist"));
        }

        $application->setDesignCode("ionic");
        $device = $application->getDevice(2);
        $device->setApplication($application);
        $device->setDownloadType("apk");
        $device->setHost($this->getHost());

        // FAST
        $result = $device->getResources();

        /** Saving log */
        $this->setLog(implode("\n", $result["log"]));

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

        if($result && ($result["success"] == true)) {
            $this->changeStatus("success");
            $this->setPath($result["path"]);
            $this->save();

            /** Success email */
            $protocol = (System_Model_Config::getValueFor("use_https")) ? "https://" : "http://";
            $url = $protocol.$this->getHost()."/".str_replace(Core_Model_Directory::getBasePathTo(""), "", $result["path"]);

            $values = array(
                "type" => __("Android APK"),
                "application_name" => $this->getName(),
                "link" => $url,
            );

            # @version 4.8.7 - SMTP
            $mail = new Siberian_Mail();
            $mail->simpleEmail("queue", "apk_queue_success", __("APK generation for App: %s", $application->getName()), $recipients, $values);
            $mail->send();

        } else {
            $this->changeStatus("failed");
            $this->save();

            /** Failed email */
            $values = array(
                "type" => __("Android APK"),
                "application_name" => $this->getName(),
            );

            # @version 4.8.7 - SMTP
            $mail = new Siberian_Mail();
            $mail->simpleEmail("queue", "apk_queue_failed", __("The requested APK generation failed: %s", $application->getName()), $recipients, $values);
            $mail->send();

        }

        return $result;
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

        foreach($results as $result) {
            # Set is building
            return array(
                "path" => str_replace(Core_Model_Directory::getBasePathTo(""), "", $result->getData("path")),
                "date" => datetime_to_format($result->getUpdatedAt())
            );
        }

        return array(
            "path" => false,
            "date" => false,
        );
    }

    /**
     * Fetch if the APK is queued
     * 
     * @param $application_id
     * @return bool
     */
    public static function getStatus($application_id) {
        $table = new self();
        $results = $table->findAll(array(
            "app_id" => $application_id,
            "status IN (?)" => array("queued", "building"),
        ));

        return ($results->count() > 0);
    }

    /**
     * @return Application_Model_ApkQueue[]
     */
    public static function getQueue() {
        $table = new self();

        $results = $table->findAll(
            array("status" => "queued"),
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
                "(build_start_time + 3600) < ?" => $current_time,
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
        $db->query("UPDATE apk_queue SET path = '' WHERE status != 'building';");
    }
    
}
