<?php

/**
 * Class Application_Model_Device
 *
 * @version 4.12.22
 *
 * @method integer getId()
 * @method $this setAppId($appId)
 * @method $this setDesignCode($designCode)
 * @method integer getStatusId()
 * @method integer getAdminId()
 * @method integer getAppId()
 * @method integer getTypeId()
 * @method string getAlias()
 * @method string getDesignCode()
 */
class Application_Model_Device extends Core_Model_Default {

    /**
     * 
     */
    const STATUS_PUBLISHED = 3;

    /**
     * @var
     */
    protected $_type;

    /**
     * @var Admin_Model_Admin|null
     */
    public $_admin = null;

    /**
     * @var array
     */
    protected static $_statuses = [
        1 => 'Waiting',
        2 => 'In Review',
        3 => 'Published',
    ];

    /**
     * @var array
     */
    protected static $_device_ids = [
        1 => 'iOS',
        2 => 'Android',
    ];

    /**
     * Application_Model_Device constructor.
     * @param array $data
     */
    public function __construct($data = []) {
        parent::__construct($data);
        $this->_db_table = "Application_Model_Db_Table_Application_Device";
    }

    /**
     * @return array
     */
    public static function getAllIds() {
        return self::$_device_ids;
    }

    /**
     * @return array
     */
    public static function getStatuses() {
        return self::$_statuses;
    }

    /**
     * @param $typeId
     * @return $this
     */
    public function loadDefault($typeId) {
        $this->setData([
            'type_id' => $typeId,
            'status_id' => 1,
            'version' => '1.0'
        ]);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getType() {
        if (is_null($this->_type)) {

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

    /**
     * @return mixed
     */
    public function getStatus() {
        return !empty(self::$_statuses[$this->getStatusId()]) ?
            self::$_statuses[$this->getStatusId()] :
            self::$_statuses[1];
    }

    /**
     * @return Admin_Model_Admin
     */
    public function getAdmin() {
        if (!$this->_admin) {
            $this->_admin = (new Admin_Model_Admin())
                ->find($this->getAdminId());
        }

        return $this->_admin;
    }

    /**
     * @param null $version
     * @return $this
     */
    public function setVersion($version = null) {
        if (!$version) {
            $version = $this->getType()->getCurrentVersion();
        }

        return $this->setData('version', $version);
    }
}
