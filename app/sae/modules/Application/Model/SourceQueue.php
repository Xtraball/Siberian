<?php

class Application_Model_SourceQueue extends Core_Model_Default {

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

        $result = $device->getResources();

        if(file_exists($result)) {
            $this->changeStatus("success");
            $this->setPath($result);

        } else {
            $this->changeStatus("failed");

        }

        $this->save();

        return $result;
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
                    "date" => $result->getFormattedUpdatedAt()
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
    
}
