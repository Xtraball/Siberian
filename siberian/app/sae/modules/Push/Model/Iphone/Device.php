<?php

/**
 * Class Push_Model_Iphone_Device
 *
 * @method integer getAppId()
 * @method string getDeviceToken()
 * @method string getDeviceUid()
 * @method Push_Model_Db_Table_Iphone_Device getTable()
 * @method $this setStatus(string $status)
 */
class Push_Model_Iphone_Device extends Core_Model_Default
{
    /**
     * @var integer
     */
    const DEVICE_TYPE = 1;

    /**
     * @var string
     */
    protected $_db_table = Push_Model_Db_Table_Iphone_Device::class;

    /**
     * @param $token
     * @return $this
     */
    public function findByToken($token)
    {
        $this->find($token, 'device_token');

        return $this;
    }

    /**
     * @param $app_id
     * @param null $topics
     * @param null $customers
     * @return Push_Model_Iphone_Device[]
     */
    public function findByAppId($app_id, $topics = null, $customers = null)
    {
        return $this->getTable()->findByAppId($app_id, $topics, $customers);
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
        $messageIds = $this->getTable()->findNotReceivedMessages($this->getDeviceUid(), $geolocated);
        $message = new Push_Model_Message();
        return !empty($messageIds) ?
            $message->findAll(['message_id IN (?)' => $messageIds]) : new Siberian_Db_Table_Rowset([]);
    }

    /**
     * @param $messageId
     * @return mixed
     */
    public function hasReceivedThisMessage($messageId)
    {
        return $this->getTable()->hasReceivedThisMessage($this->getId(), $messageId);
    }

    /**
     * Unregister Apple device
     *
     * This gets called automatically when Apple's Feedback Service responds with an invalid token.
     *
     * @access public
     */
    public function unregister()
    {
        $this->setStatus('uninstalled')->save();
    }

}
