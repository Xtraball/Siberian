<?php

namespace Push\Model\Onesignal;

require_once path('/lib/onesignal/vendor/autoload.php');

/**
 * Class Android
 * @package Push\Model\Onesignal
 */
class Android {

    /**
     * @var \Siberian_Log
     */
    public $logger;

    /**
     * @var \Push_Model_Message
     */
    public $message;

    /**
     * @var Notification
     */
    public $notification;

    /**
     * @param \Push_Model_Message $message
     */
    public function __construct(\Push_Model_Message $message)
    {
        $this->message = $message;
        $this->logger = \Zend_Registry::get('logger');

        $app_id = $this->message->getAppId();
        $app = (new \Application_Model_Application())->find($app_id);
        $onesignalAppId = $app->getOnesignalAppId();
        $onesignalAppKeyToken = $app->getOnesignalAppKeyToken();

        if (empty($onesignalAppId) || empty($onesignalAppKeyToken)) {
            // Skip configuration error
            $this->logger->info(sprintf("[CRON: %s]: ", "Missing APP_ID and/or APP_KEY_TOKEN",
                date("Y-m-d H:i:s")), "cron_push");
            $this->_log("Push\Model\Onesignal\Android", "Missing APP_ID and/or APP_KEY_TOKEN");
        }

        // OK we can create the notification
        $this->notification = new Notification($onesignalAppId, $onesignalAppKeyToken);
        $this->notification->setApplication($app);
    }

    /**
     * @return void
     */
    public function push()
    {
        $this->isSilentPush()?
            $this->notification->silentPush($this->message):
            $this->notification->regularPush($this->message);

        $this->notification->sendNotification($this->notification->notification);
    }

    public function isSilentPush(): bool
    {
        return empty($this->message->getTitle()) && empty($this->message->getText());
    }

    /**
     * log for cron
     *
     * @param $service
     * @param $message
     */
    public function _log($service, $message)
    {
        printf("%s %s[%d]: %s\n",
            date('r'), $service, getmypid(), trim($message)
        );
    }
}