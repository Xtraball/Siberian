<?php

/**
 * @category Apple Push Notification Service using PHP & MySQL
 * @package APNS
 * @author Peter Schmalfeldt <manifestinteractive@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link http://code.google.com/p/easyapns/
 */
class Push_Model_Iphone_Device extends Core_Model_Default {

    const DEVICE_TYPE = 1;

    /**
     * Constructor.
     *
     * Your iPhone App Delegate.m file will point to a PHP file with this APNS Object.  The url will end up looking something like:
     * https://secure.yourwebsite.com/apns.php?task=register&appname=My%20App&appversion=1.0.1&deviceuid=e018c2e46efe185d6b1107aa942085a59bb865d9&devicetoken=43df9e97b09ef464a6cf7561f9f339cb1b6ba38d8dc946edd79f1596ac1b0f66&devicename=My%20Awesome%20iPhone&devicemodel=iPhone&deviceversion=3.1.2&pushbadge=enabled&pushalert=disabled&pushsound=enabled
     *
     * @param object $db Database Object
     * @param array $args Optional arguments passed through $argv or $_GET
     * @access 	public
     */
    function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Push_Model_Db_Table_Iphone_Device';
    }

    public function findByToken($token) {
        $this->find($token, 'device_token');
        return $this;
    }

    /**
     * @param $app_id
     * @param null $topics
     * @param null $customers
     * @return Push_Model_Iphone_Device[]
     */
    public function findByAppId($app_id, $topics = null, $customers = null) {
        return $this->getTable()->findByAppId($app_id, $topics, $customers);
    }

    public function getTypeId() {
        return self::DEVICE_TYPE;
    }

    public function countUnreadMessages() {
        return $this->getTable()->countUnreadMessages($this->getDeviceUid());
    }

    public function findNotReceivedMessages($geolocated = null) {
        $message_ids = $this->getTable()->findNotReceivedMessages($this->getDeviceUid(), $geolocated);
        $message = new Push_Model_Message();
        return !empty($message_ids) ? $message->findAll(array('message_id IN (?)' => $message_ids)) : new Siberian_Db_Table_Rowset(array());
    }

    public function hasReceivedThisMessage($message_id) {
        return $this->getTable()->hasReceivedThisMessage($this->getId(), $message_id);
    }

    /**
     * Unregister Apple device
     *
     * This gets called automatically when Apple's Feedback Service responds with an invalid token.
     *
     * @access public
     */
    public function unregister() {
        $this->setStatus('uninstalled')->save();
    }

}