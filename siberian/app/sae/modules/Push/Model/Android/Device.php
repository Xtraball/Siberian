<?php

/**
 * Class Push_Model_Android_Device
 *
 * @method integer getAppId()
 * @method string getRegistrationId()
 */
class Push_Model_Android_Device extends Core_Model_Default
{
    /**
     *
     */
    const DEVICE_TYPE = 2;

    /**
     * Push_Model_Android_Device constructor.
     * @param array $params
     */
    function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = 'Push_Model_Db_Table_Android_Device';
    }

    /**
     * @param $reg_id
     * @return $this
     */
    public function findByRegistrationId($reg_id)
    {
        $this->find($reg_id, 'registration_id');
        return $this;
    }

    /**
     * @param $device_uid
     * @return $this
     */
    public function findByDeviceUid($device_uid)
    {
        $this->find($device_uid, 'device_uid');
        return $this;
    }

    /**
     * @param $app_id
     * @param null $topic
     * @param null $customers
     * @return mixed
     */
    public function findByAppId($app_id, $topic = null, $customers = null)
    {
        return $this->getTable()->findByAppId($app_id, $topic, $customers);
    }

    /**
     * @return int
     */
    public function getTypeId()
    {
        return self::DEVICE_TYPE;
    }

    /**
     * @return mixed
     */
    public function countUnreadMessages()
    {
        return $this->getTable()->countUnreadMessages($this->getDeviceUid());
    }

    /**
     * @param null $geolocated
     * @return Siberian_Db_Table_Rowset
     * @throws Zend_Exception
     */
    public function findNotReceivedMessages($geolocated = null)
    {
        $message_ids = $this->getTable()->findNotReceivedMessages($this->getRegistrationId(), $geolocated);
        $message = new Push_Model_Message();
        return !empty($message_ids) ? $message->findAll(['message_id IN (?)' => $message_ids]) : new Siberian_Db_Table_Rowset([]);
    }

    /**
     * @param $message_id
     * @return mixed
     */
    public function hasReceivedThisMessage($message_id)
    {
        return $this->getTable()->hasReceivedThisMessage($this->getId(), $message_id);
    }

    /**
     * Unregister device
     *
     * @access public
     */
    public function unregister()
    {
        $this->setStatus('uninstalled')->save();
    }

}