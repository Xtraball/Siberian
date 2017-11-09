<?php

class Push_Model_Android_Device extends Core_Model_Default {

    const DEVICE_TYPE = 2;

    function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Push_Model_Db_Table_Android_Device';
    }

    public function findByRegistrationId($reg_id) {
        $this->find($reg_id, 'registration_id');
        return $this;
    }

    public function findByDeviceUid($device_uid) {
        $this->find($device_uid, 'device_uid');
        return $this;
    }

    public function findByAppId($app_id, $topic=null, $customers = null) {
        return $this->getTable()->findByAppId($app_id, $topic, $customers);
    }

    public function getTypeId() {
        return self::DEVICE_TYPE;
    }

    public function countUnreadMessages() {
        return $this->getTable()->countUnreadMessages($this->getDeviceUid());
    }

    public function findNotReceivedMessages($geolocated = null) {
        $message_ids = $this->getTable()->findNotReceivedMessages($this->getRegistrationId(), $geolocated);
        $message = new Push_Model_Message();
        return !empty($message_ids) ? $message->findAll(array('message_id IN (?)' => $message_ids)) : new Siberian_Db_Table_Rowset(array());
    }

    public function hasReceivedThisMessage($message_id) {
        return $this->getTable()->hasReceivedThisMessage($this->getId(), $message_id);
    }

    /**
     * Unregister GCM device
     *
     * @access public
     */
    public function unregister() {
        $this->setStatus('uninstalled')->save();
    }

}