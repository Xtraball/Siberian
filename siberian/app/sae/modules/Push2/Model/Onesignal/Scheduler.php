<?php

namespace Push2\Model\Onesignal;

require_once path('/lib/onesignal/vendor/autoload.php');

/**
 * Class Notification
 * @package Push\Model\Onesignal
 */
class Scheduler {

    /**
     * @var \Application_Model_Application
     */
    public $application;

    /**
     * @var Message $message
     */
    public $message;

    /**
     * @var []
     */
    public $values;

    /**
     * @param $application
     * @param $values
     * @throws \Zend_Exception
     * @throws \onesignal\client\ApiException
     */
    public function __construct($application)
    {
        $this->application = $application;
    }

    public function buildMessageFromValues($values) {
        $this->values = $values;
        $this->message = (new Message())->fromArray($values);

        return $this->message;
    }

    public function importDevices($androidDevices, $iosDevices) {
        $notification = new \Push2\Model\Onesignal\Notification(
            $this->application->getOnesignalAppId(), $this->application->getOnesignalAppKeyToken());
        return $notification->importDevices($androidDevices, $iosDevices);
    }

    public function fetchNotifications() {
        $notification = new \Push2\Model\Onesignal\Notification(
            $this->application->getOnesignalAppId(), $this->application->getOnesignalAppKeyToken());
        $notification->setApplication($this->application);
        $notifications = $notification->fetchLatestNotifications();
        return $notifications;
    }

    public function send() {
        $appId = $this->application->getOnesignalAppId();
        $appKeyToken = $this->application->getOnesignalAppKeyToken();

        $notif = new Notification($appId, $appKeyToken);
        $notif->regularPush($this->message);
        $result = $notif->sendNotification();

        // Persist the message in DB, with feedback from OS
        $this->message->setOnesignalId($result->getId());
        $this->message->setExternalId($result->getExternalId());
        $this->message->setRecipients($result->getRecipients());
        $this->message->save();

        // return result
        return $result;
    }

    public function sendTest() {
        $appId = $this->application->getOnesignalAppId();
        $appKeyToken = $this->application->getOnesignalAppKeyToken();

        $notif = new Notification($appId, $appKeyToken);
        $notif->regularPush($this->message);
        $result = $notif->sendNotification();

        // return result
        return $result;
    }
}