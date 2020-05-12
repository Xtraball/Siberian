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
     * @param string $description
     * @return $this
     * @throws Zend_Exception
     */
    public function setNsCameraUd($description)
    {
        $_filtered = \Siberian\Xss::sanitize($description);

        return $this->setData('ns_camera_ud', $_filtered);
    }

    /**
     * @param string $description
     * @return $this
     * @throws Zend_Exception
     */
    public function setNsBluetoothAlwaysUd($description)
    {
        $_filtered = \Siberian\Xss::sanitize($description);

        return $this->setData('ns_bluetooth_always_ud', $_filtered);
    }

    /**
     * @param string $description
     * @return $this
     * @throws Zend_Exception
     */
    public function setNsBluetoothPeripheralUd($description)
    {
        $_filtered = \Siberian\Xss::sanitize($description);

        return $this->setData('ns_bluetooth_peripheral_ud', $_filtered);
    }

    /**
     * @param string $description
     * @return $this
     * @throws Zend_Exception
     */
    public function setNsPhotoLibraryUd($description)
    {
        $_filtered = \Siberian\Xss::sanitize($description);

        return $this->setData('ns_photo_library_ud', $_filtered);
    }

    /**
     * @param string $description
     * @return $this
     * @throws Zend_Exception
     */
    public function setNsLocationWhenInUseUd($description)
    {
        $_filtered = \Siberian\Xss::sanitize($description);

        return $this->setData('ns_location_when_in_use_ud', $_filtered);
    }

    /**
     * @param string $description
     * @return $this
     * @throws Zend_Exception
     */
    public function setNsLocationAlwaysUd($description)
    {
        $_filtered = \Siberian\Xss::sanitize($description);

        return $this->setData('ns_location_always_ud', $_filtered);
    }

    /**
     * @param string $description
     * @return $this
     * @throws Zend_Exception
     */
    public function setNsLocationAlwaysAndWhenInUseUd($description)
    {
        $_filtered = \Siberian\Xss::sanitize($description);

        return $this->setData('ns_location_always_and_when_in_use_ud', $_filtered);
    }

    /**
     * @param string $description
     * @return $this
     * @throws Zend_Exception
     */
    public function setNsMotionUd($description)
    {
        $_filtered = \Siberian\Xss::sanitize($description);

        return $this->setData('ns_motion_ud', $_filtered);
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
     * @return Application_Model_Device_Abstract
     */
    public function getType() {
        if (is_null($this->_type)) {

            $class = sprintf('%s_%s_%s',
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
}
