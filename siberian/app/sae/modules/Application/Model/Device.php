<?php

class Application_Model_Device extends Core_Model_Default {

    const STATUS_PUBLISHED = 3;

    protected $_type;

    protected static $_statuses = array(
        1 => "Waiting",
        2 => "In Review",
        3 => "Published"
    );
    protected static $_device_ids = array(
        1 => 'iOS',
        2 => 'Android',
    );

    public function __construct($data = array()) {
        parent::__construct($data);
        $this->_db_table = "Application_Model_Db_Table_Application_Device";
    }

    public static function getAllIds() {
        return self::$_device_ids;
    }

    public static function getStatuses() {
        return self::$_statuses;
    }

    public function loadDefault($type_id) {
        $this->setData(array(
            "type_id" => $type_id,
            "status_id" => 1,
            "version" => "1.0"
        ));

        return $this;
    }

    public function getType() {
        if(is_null($this->_type)) {

            $class = sprintf("%s_%s_%s",
                get_class(),
                ucfirst($this->getDesignCode()),
                ucfirst(strtolower(self::$_device_ids[$this->getTypeId()]))
            );

            $this->_type = new $class();
            $this->_type->setDevice($this);
        }

        return $this->_type;
    }

    public function getName() {

        $name = '';
        if($this->getTypeId()) {
            $name = !empty(self::$_device_ids[$this->getTypeId()]) ? self::$_device_ids[$this->getTypeId()] : '';
        }

        return $name;
    }

    public function getStoreName() {
        $name = '';
        if($this->getTypeId()) {
            $name = $this->getType()->getStoreName();
        }

        return $name;
    }

    public function getBrandName() {
        $name = '';
        if($this->getTypeId()) {
            $name = $this->getType()->getBrandName();
        }

        return $name;
    }

    public function getTmpFolderName() {

        $app_id = $this->getAppId();
        $alias = $this->getAlias();
        
        if($alias != $app_id) {
            $alias .= "-$app_id";
        }

        # TG-196, remove blank characters
        $alias = preg_replace('/\s+/', '-', trim($alias));
        
        return $alias;
    }

    public function isPublished() {
        return $this->getStatusId() == self::STATUS_PUBLISHED;
    }

    public function getResources() {
        return $this->getType()->getResources($this->getApplication());
    }

    public function unsetStatus() {
        $this->_status = null;
    }

    public function getStatus() {
        return !empty(self::$_statuses[$this->getStatusId()]) ?
            self::$_statuses[$this->getStatusId()] :
            self::$_statuses[1];
    }

    public function getAdmin() {

        if(!$this->_admin) {
            $this->_admin = new Admin_Model_Admin();
            $this->_admin->find($this->getAdminId());
        }

        return $this->_admin;
    }

    public function setVersion($version = null) {
        if(!$version) $version = $this->getType()->getCurrentVersion();
        $this->setData('version', $version);
        return $this;
    }
}
