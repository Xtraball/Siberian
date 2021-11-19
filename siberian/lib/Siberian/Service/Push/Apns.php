<?php

use Siberian\Hook;
use Dashi\Apns2\Connection as Apns2Connection;
use Dashi\Apns2\MessageAPSBody as Apns2MessageAPSBody;
use Dashi\Apns2\Message as Apns2Message;
use Dashi\Apns2\Options as Apns2Options;

/**
 * Class Siberian_Service_Push_Apns
 */
class Siberian_Service_Push_Apns
{
    /**
     *
     */
    const ROOT_CA = "/var/apps/certificates/root_ca.pem";

    /**
     * @var \Dashi\Apns2\Connection
     */
    public $connection;

    /**
     * @var \Dashi\Apns2\Response[]
     */
    public $responses;

    /**
     * @var string[]
     */
    public $recipients;

    /**
     * @var Dashi\Apns2\Message
     */
    public $message;

    /**
     * @var Dashi\Apns2\Options
     */
    public $options;

    /**
     * @var mixed
     */
    public $logger;

    /**
     * Siberian_Service_Push_Apns constructor.
     * @param $sProviderCertificateFile
     * @param null $sandbox
     */
    public function __construct($sProviderCertificateFile, $sandbox = null)
    {
        $this->logger = Zend_Registry::get("logger");
        $this->connection = new Apns2Connection(
            [
                'sandbox' => false,
                'cert-path' => $sProviderCertificateFile
            ]);
    }

    /**
     * @param $messagePayload
     * @param $bundleId
     * @param $devices
     * @throws Zend_Exception
     */
    public function addMessage($messagePayload, $bundleId, $devices)
    {
        $aps = new Apns2MessageAPSBody();
        $aps->alert->title = $messagePayload->getTitle();
        $aps->alert->body = $messagePayload->getText();
        $aps->alert->actionLocKey = p__('push', 'Read');
        $aps->alert->cover = $messagePayload->getCoverUrl();
        $aps->alert->open_webview = $messagePayload->getCoverUrl();
        $aps->sound = 'sb_beep4.caf';
        $aps->badge = 1;
        $aps->message_id = \Ramsey\Uuid\v4();

        # Action
        $application = new Application_Model_Application();
        $application->find($messagePayload->getAppId());

        if (is_numeric($messagePayload->getActionValue())) {
            $option_value = new Application_Model_Option_Value();
            $option_value->find($messagePayload->getActionValue());

            $mobileUri = $option_value->getMobileUri();
            if (preg_match('/^goto\/feature/', $mobileUri)) {
                $actionUrl = sprintf("/%s/%s/value_id/%s",
                    $application->getKey(),
                    $mobileUri,
                    $option_value->getId());
            } else {
                $actionUrl = sprintf("/%s/%sindex/value_id/%s",
                    $application->getKey(),
                    $option_value->getMobileUri(),
                    $option_value->getId());
            }
        } else {
            $actionUrl = $messagePayload->getActionValue();
        }

        $aps->alert->action_value = $actionUrl;
        $aps->alert->open_webview = !is_numeric($messagePayload->getActionValue());

        $message = new Apns2Message();
        $message->aps = $aps;

        $this->options = new Apns2Options();
        $this->options->apnsId = $aps->message_id;
        $this->options->apnsTopic = $bundleId;
        $this->options->apnsPushType = 'alert';
        $this->options->apnsPriority = 10;
        $this->options->apnsExpiration = 0;

        // Trigger an event when the push message is parsed!
        $result = Hook::trigger('push.message.ios.parsed',
            [
                'message' => $message,
                'application' => $application
            ]);

        // Recipients
        foreach ($devices as $device) {
            if ($device->getPushAlert() === 'enabled') {
                $this->recipients[] = $device->getDeviceToken();
            }
        }

        $this->message = $result['message'];
    }

    /**
     * @param $message
     */
    public function add($message)
    {
        $this->recipients[] = $message->_aDeviceToken;
    }

    /**
     * @throws Exception
     */
    public function sendAll()
    {
        $this->responses = $this->connection->send(
            $this->recipients,
            $this->message,
            $this->options
        );
        $this->connection->close();
    }

    /**
     * @param $message
     */
    public function _log($message)
    {
        // @todo logger
    }
}
