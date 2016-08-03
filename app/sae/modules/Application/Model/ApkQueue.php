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

        $result = $device->getResources();

        /** Saving log */
        $this->setLog(implode("\n", $result["log"]));

        if($result && ($result["success"] == true)) {
            $this->changeStatus("success");
            $this->setPath($result["path"]);

        } else {
            $this->changeStatus("failed");

        }

        $this->save();

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
                "date" => $result->getFormattedUpdatedAt()
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
    
}
