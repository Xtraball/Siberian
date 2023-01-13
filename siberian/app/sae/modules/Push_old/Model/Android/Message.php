<?php

use Siberian\Hook;

/**
 * Class Push_Model_Android_Message
 */
class Push_Model_Android_Message
{
    /**
     * @var \Siberian\CloudMessaging\Sender\Fcm
     */
    private $service_fcm = null;

    /**
     * @var null|Push_Model_Message
     */
    private $message = null;

    /**
     * @var null|Siberian_Log
     */
    private $logger = null;

    /**
     * Push_Model_Android_Message constructor.
     * @param \Siberian\CloudMessaging\Sender\Fcm $serviceFcm
     * @throws Zend_Exception
     */
    public function __construct($serviceFcm)
    {
        $this->service_fcm = $serviceFcm;
        $this->logger = Zend_Registry::get("logger");
    }

    /**
     * @param Push_Model_Message $message
     */
    public function setMessage(Push_Model_Message $message)
    {
        $this->message = $message;
    }

    /**
     * @return Push_Model_Message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return \Siberian\Service\Push\CloudMessaging\Message
     * @throws Zend_Exception
     */
    public function buildMessage()
    {
        $messagePayload = new \Siberian\Service\Push\CloudMessaging\Message();

        $message = $this->getMessage();

        $application = new Application_Model_Application();
        $application->find($message->getAppId());

        $pushColor = strtoupper($application->getAndroidPushColor() ?? '#0099C7');

        if (is_numeric($message->getActionValue())) {
            $option_value = new Application_Model_Option_Value();
            $option_value->find($message->getActionValue());

            // In case we use only value_id
            if (!$application || !$application->getId()) {
                $application = (new Application_Model_Application())->find($option_value->getAppId());
            }

            $mobileUri = $option_value->getMobileUri();
            if (preg_match('/^goto\/feature/', $mobileUri)) {
                $action_url = sprintf("/%s/%s/value_id/%s",
                    $application->getKey(),
                    $mobileUri,
                    $option_value->getId());
            } else {
                $action_url = sprintf("/%s/%sindex/value_id/%s",
                    $application->getKey(),
                    $option_value->getMobileUri(),
                    $option_value->getId());
            }
        } else {
            $action_url = $message->getActionValue();
        }

        $coverUrl = $message->getCoverUrl();
        if (!preg_match("#^https?://#", $coverUrl)) {
            $coverUrl = $message->getData("base_url") . $coverUrl;
        }

        // Double check after altering data ...
        if ($coverUrl === $message->getData("base_url")) {
            $coverUrl = "";
        }

        $messagePayload
            ->setMessageId($message->getMessageId() . uniqid('push_fcm_', true))
            ->setTitle($message->getTitle())
            ->setMessage($message->getText())
            ->setGeolocation($message->getLatitude(), $message->getLongitude(), $message->getRadius())
            ->setCover($coverUrl, $coverUrl, $message->getText())
            ->setDelayWithIdle(false)
            ->setTimeToLive(0)
            ->setSendUntil($message->getSendUntil() ? $message->getSendUntil() : "0")
            ->setActionValue($action_url);

        if ($message->getForceAppRoute() === true) {
            $messagePayload->setOpenWebview(false);
        } else {
            $messagePayload->setOpenWebview(!is_numeric($message->getActionValue()));
        }

        # Priority to custom image
        $customImage = $message->getCustomImage();
        $path_custom_image = path("/images/application" . $customImage);
        if (strpos($customImage, '/images/assets') === 0 &&
            is_file(path($customImage))) {
            $messagePayload->setImage($message->getData('base_url') . $customImage);
        } else if (is_readable($path_custom_image) && !is_dir($path_custom_image)) {
            $messagePayload->setImage($message->getData('base_url') . '/images/application' . $customImage);
        } else {
            # Default application image
            $application_image = $application->getAndroidPushImage();
            if (!empty($application_image)) {
                $messagePayload->setImage($message->getData("base_url") .
                    "/images/application" . $application_image);
            }
        }

        if ($message->getLongitude() &&
            $message->getLatitude()) {
            $messagePayload->contentAvailable(true);
        }

        // Sound Legacy HTTP Payload!
        $messagePayload->addData('soundname', 'sb_beep4');

        // High priority!
        $messagePayload->priority('high');

        // Silent push enforced!
        $isSilentPush = $message->getIsSilent();

        // Check for "implicit" silent
        $noTitle = trim($messagePayload->getData()['title']);
        $noBody = trim($messagePayload->getData()['message']);
        if (empty($noTitle) && empty($noBody)) {
            $isSilentPush = true;
        }

        // Notification for FCM latest
        if (!$isSilentPush) {
            $notification = new \Siberian\CloudMessaging\Notification();
            $notification->title($messagePayload->getData()['title']);
            $notification->body($messagePayload->getData()['message']);
            $notification->sound('sb_beep4');
            $notification->icon('ic_icon');
            $notification->color($pushColor);
            $notification->notificationPriority('high');
            $messagePayload->notification($notification);
        }

        // Trigger an event when the push message is parsed,
        $result = Hook::trigger('push.message.android.parsed',
            [
                'message' => $messagePayload,
                'application' => $application
            ]);

        $messagePayload = $result['message'];

        return $messagePayload;
    }


}
