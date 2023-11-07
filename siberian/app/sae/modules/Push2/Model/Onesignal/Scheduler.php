<?php

namespace Push2\Model\Onesignal;

use Push2\Model\Onesignal\Targets\AbstractTarget;
use Siberian\Json;

require_once path('/lib/onesignal/vendor/autoload.php');

/**
 * Class Notification
 * @package Push\Model\Onesignal
 */
class Scheduler
{

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

    public function buildMessageFromValues($values)
    {
        $this->values = $values;
        $this->message = (new Message())->fromArray($values);

        return $this->message;
    }

    public function importDevices($androidDevices, $iosDevices)
    {
        $notification = new \Push2\Model\Onesignal\Notification(
            $this->application->getOnesignalAppId(), $this->application->getOnesignalAppKeyToken());
        return $notification->importDevices($androidDevices, $iosDevices);
    }

    public function fetchNotifications()
    {
        $notification = new \Push2\Model\Onesignal\Notification(
            $this->application->getOnesignalAppId(), $this->application->getOnesignalAppKeyToken());
        $notification->setApplication($this->application);
        $notificationSlice = $notification->fetchLatestNotifications(null, 200);
        $notifications = $notificationSlice->getNotifications();

        $mappedNotifs = [];
        foreach ($notifications as $notification) {
            $mappedNotifs[$notification->getId()] = $notification;
        }

        $messages = (new Message())->findAll([
            'app_id' => $this->application->getId(),
            'is_test' => 0,
            'is_for_module' => 0
        ], 'created_at DESC', [
            'limit' => 50
        ]);

        foreach ($messages as $message) {
            if (array_key_exists($message->getOnesignalId(), $mappedNotifs)) {
                $notification = $mappedNotifs[$message->getOnesignalId()];

                $message->setNotification($notification);
            } else {
                $message->setNotification(null);
            }
        }

        return $messages;
    }

    /**
     * @return \onesignal\client\model\SegmentSlice
     * @throws \onesignal\client\ApiException
     */
    public function fetchSegments()
    {
        $notification = new \Push2\Model\Onesignal\Notification(
            $this->application->getOnesignalAppId(), $this->application->getOnesignalAppKeyToken());
        $notification->setApplication($this->application);

        return $notification->fetchSegments();
    }

    public function send()
    {
        $appId = $this->application->getOnesignalAppId();
        $appKeyToken = $this->application->getOnesignalAppKeyToken();

        $notif = new Notification($appId, $appKeyToken);
        $notif->regularPush($this->message);
        $result = $notif->sendNotification();

        // Persist the message in DB, with feedback from OS
        $this->message->setOnesignalId($result->getId());
        $this->message->setExternalId($result->getExternalId());
        $this->message->setRecipients($result->getRecipients() ?? 0);
        $this->message->save();

        // Saving individual history!
        if ($this->message->getIsIndividual()) {
            $uniquePlayers = array_unique($this->message->getPlayerIds());
            foreach ($uniquePlayers as $player) {
                $playerMessage = (new PlayerMessage())->find([
                    'player_id' => $player,
                    'message_id' => $this->message->getId()
                ]);
                $playerMessage->setPlayerId($player);
                $playerMessage->setMessageId($this->message->getId());
                $playerMessage->save();
            }
        }

        // return result
        return $result;
    }

    /**
     * @param $customerId
     * @return \onesignal\client\model\CreateNotificationBadRequestResponse|\onesignal\client\model\CreateNotificationSuccessResponse
     */
    public function sendToCustomer($customerId)
    {
        // Find playerIds for the customer
        $playerIds = [];
        $players = (new Player())->getTable()->findAll([
            'customer_id' => $customerId
        ]);
        foreach ($players as $player) {
            $playerIds[] = $player->getPlayerId();
        }

        $this->message->setIsIndividual(1);
        $this->message->setPlayerIds($playerIds);
        $this->message->checkTargets();

        return $this->send();
    }

    /**
     * @param AbstractTarget $targets
     * @return \onesignal\client\model\CreateNotificationBadRequestResponse|\onesignal\client\model\CreateNotificationSuccessResponse
     */
    public function sendToTargets(AbstractTarget $targets)
    {
        $this->message->setIsIndividual(false);
        $this->message->clearTargets();
        $this->message->addTargets($targets);

        return $this->send();
    }

    public function sendTest()
    {
        $appId = $this->application->getOnesignalAppId();
        $appKeyToken = $this->application->getOnesignalAppKeyToken();

        $notif = new Notification($appId, $appKeyToken);
        $notif->regularPush($this->message);
        $result = $notif->sendNotification();

        // return result
        return $result;
    }
}