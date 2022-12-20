<?php

namespace Push\Model\Onesignal;

use Push\Model\Onesignal\Targets\Android;
use Push\Model\Onesignal\Targets\Ios;
use Push\Model\Onesignal\Targets\Player;
use Push\Model\Onesignal\Targets\Segment;
use Push\Model\Onesignal\Targets\Web;

require_once path('/lib/onesignal/vendor/autoload.php');

/**
 * Class Notification
 * @package Push\Model\Onesignal
 */
class Notification {

    public $APP_ID;
    public $APP_KEY_TOKEN;
    //public $USER_KEY_TOKEN = '<YOUR_USER_KEY_TOKEN>';

    /**
     * @var \onesignal\client\api\DefaultApi
     */
    public $apiInstance;

    /**
     * Notification constructor.
     * @param $app_id
     * @param $app_key_token
     */
    public function __construct($app_id, $app_key_token)
    {
        $this->APP_ID = $app_id;
        $this->APP_KEY_TOKEN = $app_key_token;

        $config = onesignal\client\Configuration::getDefaultConfiguration()
            ->setAppKeyToken($this->APP_KEY_TOKEN);

        $this->apiInstance = new onesignal\client\api\DefaultApi(
            new GuzzleHttp\Client(),
            $config
        );
    }

    /**
     * @param $enContent
     * @return \onesignal\client\model\Notification
     */
    public function createNotification($enContent): onesignal\client\model\Notification
    {
        $content = new onesignal\client\model\StringMap();
        //$content->setEn($enContent);

        //$content->set

        $notification = new onesignal\client\model\Notification();
        $notification->setAppId(self::APP_ID);
        //$notification->setContents($content);
        $notification->setIncludedSegments(['Subscribed Users']);
        $notification->setContentAvailable(true);
        $notification->setData([
            'cabride' => true,
            'page' => 42,
            'action' => 'show'
        ]);

        return $notification;
    }

    /**
     * @param $enContent
     * @return \onesignal\client\model\Notification
     */
    public function silentPush($data, $targets): \onesignal\client\model\Notification
    {
        $notification = new \onesignal\client\model\Notification();
        $notification->setAppId($this->APP_ID);



        $notification->setIncludePlayerIds();
        //$notification->setIncludePlayerIds()
        //$notification->setIncludedSegments(['Subscribed Users']);
        $notification->setContentAvailable(true);
        $notification->setData([
            'cabride' => true,
            'page' => 42,
            'action' => 'show'
        ]);

        return $notification;
    }

    /**
     * Agnostic method to correctly set the users/devices/segments depending on the object type
     *
     * @param \onesignal\client\model\Notification $notification
     * @param $targets
     */
    public function setTargets(\onesignal\client\model\Notification $notification, $targets)
    {
        switch (get_class($targets)) {
            case Android::class:
                $notification->setIncludeAndroidRegIds($targets->getTargets());
                break;

            case Ios::class:
                $notification->setIncludeIosTokens($targets->getTargets());
                break;

            case Web::class:
                $notification->setIncludeChromeRegIds($targets->getTargets());
                break;

            case Segment::class:
                $notification->setIncludedSegments($targets->getTargets());
                break;

            case Player::class:
                $notification->setIncludePlayerIds($targets->getTargets());
                break;
        }
    }

    /**
     * @param onesignal\client\model\Notification $notification
     * @throws \onesignal\client\ApiException
     */
    public function sendNotification(onesignal\client\model\Notification $notification) {
        $result = $this->apiInstance->createNotification($notification);
    }
}