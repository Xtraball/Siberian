<?php

namespace Push2\Model\Onesignal;

use Push2\Model\Onesignal\Targets\AbstractTarget;
use Push2\Model\Onesignal\Targets\Android;
use Push2\Model\Onesignal\Targets\Ios;
use Push2\Model\Onesignal\Targets\Player;
use Push2\Model\Onesignal\Targets\Segment;
use Push2\Model\Onesignal\Targets\Web;

use Siberian\Hook as Hook;

require_once path('/lib/onesignal/vendor/autoload.php');

/**
 * Class Notification
 * @package Push\Model\Onesignal
 */
class Notification
{

    public $application;

    public $APP_ID;
    public $APP_KEY_TOKEN;

    /**
     * @var \onesignal\client\api\DefaultApi
     */
    public $apiInstance;
    /**
     * @var \onesignal\client\model\Notification
     */
    public $notification;

    /**
     * Notification constructor.
     * @param $app_id
     * @param $app_key_token
     */
    public function __construct($app_id, $app_key_token)
    {
        $this->APP_ID = $app_id;
        $this->APP_KEY_TOKEN = $app_key_token;

        $config = \onesignal\client\Configuration::getDefaultConfiguration()
            ->setAppKeyToken($this->APP_KEY_TOKEN);

        $this->apiInstance = new \onesignal\client\api\DefaultApi(
            new \GuzzleHttp\Client(),
            $config
        );
    }

    /**
     * @param $application
     * @return void
     */
    public function setApplication($application)
    {
        $this->application = $application;
    }

    public function fetchLatestNotifications($appId = null, $limit = 50, $offset = 0, $includePlayerIds = 1)
    {
        return $this->apiInstance->getNotifications(
            $appId ?? $this->APP_ID, $limit, $offset, $includePlayerIds);
    }

    /**
     * @param $appId
     * @return \onesignal\client\model\SegmentSlice
     * @throws \onesignal\client\ApiException
     */
    public function fetchSegments($appId = null)
    {
        return $this->apiInstance->getSegments($appId ?? $this->APP_ID);
    }

    /**
     * @param Message $message
     * @return void
     * @throws \Zend_Exception
     */
    public function regularPush(\Push2\Model\Onesignal\Message $message)
    {
        $additionalData = false;

        $title = new \onesignal\client\model\StringMap();
        $title->setEn($message->getTitle());

        $body = new \onesignal\client\model\StringMap();
        $body->setEn($message->getBody());

        $newUuid = \Siberian\UUID::v4();

        $this->notification = new \onesignal\client\model\Notification();
        $this->notification->setExternalId($newUuid);
        $this->notification->setAppId($this->APP_ID);
        $this->notification->setHeadings($title);
        $this->notification->setContents($body);

        // Ensure badge number increases
        $this->notification->setIosBadgeType("Increase");
        $this->notification->setIosBadgeCount(1);

        $this->notification->setSendAfter($message->getSendAfter());
        $this->notification->setDelayedOption($message->getDelayedOption());
        $this->notification->setDeliveryTimeOfDay($message->getDeliveryTimeOfDay());

        // Cover image, if exists!
        $bigPicture = $message->getBigPicture();
        if (!empty($bigPicture)) {
            $this->notification->setBigPicture($bigPicture);

            $iosAttachments = new \stdClass();
            $iosAttachments->big_picture = $bigPicture;
            $this->notification->setIosAttachments($iosAttachments);

            $additionalData = true;
        }

        // Push icon color
        //$pushColor = strtoupper($this->application->getAndroidPushColor() ?? '#0099C7');

        $actionUrl = $message->getActionLink();
        if (!empty($actionUrl)) {
            $additionalData = true;
        }

        if ($additionalData) {
            $this->notification->setContentAvailable(true);
            $this->notification->setData([
                'title' => $message->getTitle(),
                'body' => $message->getBody(),
                'soundname' => 'sb_beep4',
                'action_value' => $actionUrl,
                'cover' => $bigPicture,
                'open_webview' => stripos($actionUrl, 'http') !== false,
            ]);
        }

        $this->notification->setAndroidSound('sb_beep4');
        $this->notification->setPriority(10);

        // Geolocated push
        if ($message->getUseLocation()) {
            $filterLocation = new \stdClass();
            $filterLocation->field = 'location';
            $filterLocation->radius = $message->getRadius();
            $filterLocation->lat = $message->getLatitude();
            $filterLocation->long = $message->getLongitude();

            $this->notification->setFilters([$filterLocation]);
        } else {
            $this->setTargets($message->getTargets());
        }

        //
        $result = Hook::trigger('push.message.android.parsed',
            [
                'notification' => $this->notification,
                'application' => $this->application
            ]);

        $this->notification = $result['notification'];
    }

    /**
     * @param \Push2\Model\Message $message
     * @return \onesignal\client\model\Notification
     */
    public function silentPush(\Push2\Model\Message $message): \onesignal\client\model\Notification
    {
        $notification = new \onesignal\client\model\Notification();
        $notification->setAppId($this->APP_ID);

        $this->setTargets($notification, $message->getTargets());

        //$notification->setContentAvailable(true);
        //$notification->setData([
        //    'cabride' => true,
        //    'page' => 42,
        //    'action' => 'show'
        //]);

        return $notification;
    }

    /**
     * Agnostic method to correctly set the users/devices/segments depending on the object type
     *
     * @param AbstractTarget[] $targets
     */
    public function setTargets(array $targets = [])
    {
        foreach ($targets as $target) {
            switch (get_class($target)) {
                case Android::class:
                    $this->notification->setIncludeAndroidRegIds($target->getTargets());
                    break;

                case Ios::class:
                    $this->notification->setIncludeIosTokens($target->getTargets());
                    break;

                case Web::class:
                    $this->notification->setIncludeChromeRegIds($target->getTargets());
                    break;

                case Segment::class:
                    $this->notification->setIncludedSegments($target->getTargets());
                    break;

                case Player::class:
                    $this->notification->setIncludePlayerIds($target->getTargets());
                    break;
            }
        }
    }

    public function importDevices($androidDevices, $iosDevices)
    {

        $counter = 0;
        foreach ($androidDevices as $androidDevice) {
            try {
                $player = new \onesignal\client\model\Player();
                $player->setAppId($this->APP_ID);
                $player->setDeviceType(1);
                $player->setIdentifier($androidDevice['registration_id']);
                $player->setNotificationTypes(1);
                if (!empty($androidDevice['customer_id'])) {
                    $external_user_id = implode_polyfill('_', ['os', 'customer', $androidDevice['app_id'], $androidDevice['customer_id']]);
                    $player->setExternalUserId($external_user_id);
                } else {
                    $external_user_id = implode_polyfill('_', ['os', 'anonymous', $androidDevice['app_id'], $androidDevice['device_uid']]);
                    $player->setExternalUserId($external_user_id);
                }

                $this->apiInstance->createPlayer($player);
                $counter++;
            } catch (\Exception $e) {
                // continue
            }
        }

        foreach ($iosDevices as $iosDevice) {
            try {
                $player = new \onesignal\client\model\Player();
                $player->setAppId($this->APP_ID);
                $player->setDeviceType(0);
                $player->setIdentifier($iosDevice['device_token']);
                $player->setDeviceModel($iosDevice['device_model']);
                $player->setDeviceOs($iosDevice['device_version']);
                $player->setNotificationTypes(1);
                if (!empty($iosDevice['customer_id'])) {
                    $external_user_id = implode_polyfill('_', ['os', 'customer', $iosDevice['app_id'], $iosDevice['customer_id']]);
                    $player->setExternalUserId($external_user_id);
                } else {
                    $external_user_id = implode_polyfill('_', ['os', 'anonymous', $iosDevice['app_id'], $iosDevice['device_uid']]);
                    $player->setExternalUserId($external_user_id);
                }

                $this->apiInstance->createPlayer($player);
                $counter++;
            } catch (\Exception $e) {
                // continue
            }
        }

        return $counter;
    }

    /**
     * @throws \onesignal\client\ApiException
     */
    public function sendNotification()
    {
        $result = $this->apiInstance->createNotification($this->notification);
        file_put_contents(path("/var/log/onesignal.log"), print_r($result, true), FILE_APPEND);
        return $result;
    }
}