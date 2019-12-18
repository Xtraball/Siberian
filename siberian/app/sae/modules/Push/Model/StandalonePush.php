<?php

namespace Push\Model;

use Core\Model\Base;

use Push_Model_Certificate as Certificate;
use Push_Model_Ios_Message as IosMessage;
use Push_Model_Android_Message as AndroidMessage;
use Push_Model_Firebase as Firebase;
use Push_Model_Message as Message;
use Push_Model_Android_Device as AndroidDevice;
use Push_Model_Iphone_Device as IosDevice;

use Siberian\Json;
use Siberian\Cron;
use Siberian_Service_Push_Apns as Apns;
use Siberian\Exception;
use Siberian\CloudMessaging\Sender\Fcm;

use Zend_Registry as Registry;

/**
 * Class StandalonePush
 * @package Push\Model
 *
 * @method string getIcon()
 * @method $this setValueId(integer $valueId)
 * @method $this setAppId(integer $appId)
 * @method $this setTokens(string $tokens)
 * @method $this setPushDeviceId(integer $deviceId)
 * @method $this setTarget($target)
 * @method $this setStatus(string $status)
 * @method $this setTitle(string $title)
 * @method $this setCover(string $cover)
 * @method $this setMessage(string $message)
 * @method $this setActionValue(mixed $actionValue)
 * @method $this setSendAt(integer $timestamp)
 * @method $this setMessageJson(string $jsonMessage)
 * @method string getTokens()
 * @method string getMessageJson()
 * @method $this[] findAll($values, $order = null, $params = [])
 */
class StandalonePush extends Base
{
    /**
     * @var string[]
     */
    public $tokens;

    /**
     * @var AndroidDevice[]
     */
    public $androidDevices;

    /**
     * @var IosDevice[]
     */
    public $iosDevices;

    /**
     * StandalonePush constructor.
     * @param array $params
     * @throws \Zend_Exception
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = 'Push\Model\Db\Table\StandalonePush';
    }

    /**
     * @param array $tokens
     * @return StandalonePush
     * @throws \Zend_Exception
     */
    public static function buildFromTokens (array $tokens = [])
    {
        $instance = new self();

        $instance->tokens = $tokens;

        $instance->androidDevices = (new AndroidDevice())->findAll([
            'registration_id IN (?)' => $tokens
        ]);

        $instance->iosDevices = (new IosDevice())->findAll([
            'device_token IN (?)' => $tokens
        ]);

        return $instance;
    }

    /**
     * @param $title
     * @param $text
     * @param $cover
     * @param integer|string|null $actionValue
     * @param integer|null $valueId
     * @param integer|null $appId
     * @param boolean $forceAppRoute
     * @throws \Zend_Exception
     */
    public function sendMessage($title,
                                $text,
                                $cover,
                                $actionValue = null,
                                $valueId = null,
                                $appId = null,
                                $forceAppRoute = true)
    {
        // Save push in custom history
        $jsonMessage = [
            'title' => $title,
            'text' => $text,
            'cover' => $cover,
            'actionValue' => $actionValue,
            'forceAppRoute' => $forceAppRoute,
        ];

        $this
            ->setValueId($valueId)
            ->setAppId($appId)
            ->setTokens(Json::encode($this->tokens))
            ->setTitle($title)
            ->setMessage($text)
            ->setCover($cover)
            ->setActionValue($actionValue)
            ->setStatus('sent')
            ->setMessageJson(Json::encode($jsonMessage))
            ->save();

        $message = self::buildMessage($title, $text, $cover, $actionValue, $forceAppRoute);

        // try/catch are already handled inside sendPush
        foreach ($this->androidDevices as $androidDevice) {
            $this->sendPush($androidDevice, $message);
        }

        // try/catch are already handled inside sendPush
        foreach ($this->iosDevices as $iosDevice) {
            $this->sendPush($iosDevice, $message);
        }
    }

    /**
     * @param string $title
     * @param string $text
     * @param string $cover
     * @param integer $sendAt
     * @param mixed|null $actionValue
     * @param integer|null $valueId
     * @param integer|null $appId
     * @param boolean $forceAppRoute
     * @throws \Zend_Exception
     * @throws Exception
     */
    public function scheduleMessage($title,
                                    $text,
                                    $cover,
                                    $sendAt,
                                    $actionValue = null,
                                    $valueId = null,
                                    $appId = null,
                                    $forceAppRoute = true)
    {
        // Checking timestamp!
        if ($sendAt < time()) {
            throw new Exception(p__('push', 'Error: %s must be a timestamp in the past.', '\$sentAt'));
        }

        $jsonMessage = [
            'title' => $title,
            'text' => $text,
            'cover' => $cover,
            'actionValue' => $actionValue,
            'forceAppRoute' => $forceAppRoute,
        ];

        // Save push in custom history
        $this
            ->setValueId($valueId)
            ->setAppId($appId)
            ->setTokens(Json::encode($this->tokens))
            ->setTitle($title)
            ->setMessage($text)
            ->setCover($cover)
            ->setActionValue($actionValue)
            ->setSendAt($sendAt)
            ->setStatus('scheduled')
            ->setMessageJson(Json::encode($jsonMessage))
            ->save();
    }

    /**
     * @param AndroidDevice|IosDevice $device
     * @param Message $message
     * @throws \Zend_Exception
     */
    public function sendPush($device, $message)
    {
        $logger = Registry::get('logger');
        $appId = $device->getAppId();

        $iosCertificate = path(Certificate::getiOSCertificat($appId));

        if ($device instanceof IosDevice) {
            try {
                $message->setToken($device->getDeviceToken());

                if (is_file($iosCertificate)) {
                    $instance = new IosMessage(new Apns(null, $iosCertificate));
                    $instance->setMessage($message);
                    $instance->push();
                } else {
                    throw new Exception("You must provide an APNS Certificate for the App ID: {$appId}");
                }
            } catch (\Exception $e) {
                $logger->err(
                    sprintf('[Push Standalone: %s]: %s',
                        date('Y-m-d H:i:s'),
                        $e->getMessage()
                    ),
                    'standalone_push');
            }
        } else if ($device instanceof AndroidDevice) {
            try {
                $message->setToken($device->getRegistrationId());

                $credentials = (new Firebase())
                    ->find('0', 'admin_id');

                $fcmKey = $credentials->getServerKey();
                $fcmInstance = null;
                if (!empty($fcmKey)) {
                    $fcmInstance = new Fcm($fcmKey);
                } else {
                    // Only FCM is mandatory by now!
                    throw new Exception('You must provide FCM Credentials');
                }

                $instance = new AndroidMessage($fcmInstance, null);
                $instance->setMessage($message);
                $instance->push();

            } catch (\Exception $e) {
                $logger->err(
                    sprintf('[Push Standalone: %s]: %s',
                        date('Y-m-d H:i:s'),
                        $e->getMessage()
                    ),
                    'standalone_push');
            }
        }
    }

    /**
     * @param Cron $cron
     * @throws \Zend_Exception
     */
    public static function sendScheduled (Cron $cron)
    {
        $cron->log(sprintf('[Standalone Push]: time %s.', time()));

        $messagesToSend = (new self())->findAll([
            'status = ?' => 'scheduled',
            'send_at < ?' => time()
        ]);

        if ($messagesToSend->count() === 0) {
            $cron->log('[Standalone Push]: no scheduled push, done.');
            return;
        }

        $cron->log(sprintf('[Standalone Push]: there is %s messages to send.', $messagesToSend->count()));

        foreach ($messagesToSend as $messageToSend) {
            $pushMessage = Json::decode($messageToSend->getMessageJson());
            $tokens = Json::decode($messageToSend->getTokens());

            $cron->log(sprintf('[Standalone Push]: message %s.', $pushMessage['title']));
            $cron->log(sprintf('[Standalone Push]: sending to %s.', implode(', ', $tokens)));

            $instance = self::buildFromTokens($tokens);

            $message = self::buildMessage(
                $pushMessage['title'],
                $pushMessage['text'],
                $pushMessage['cover'],
                $pushMessage['action_value'],
                $pushMessage['forceAppRoute']);

            // try/catch are already handled inside sendPush
            foreach ($instance->androidDevices as $androidDevice) {
                $instance->sendPush($androidDevice, $message);
            }

            // try/catch are already handled inside sendPush
            foreach ($instance->iosDevices as $iosDevice) {
                $instance->sendPush($iosDevice, $message);
            }

            $messageToSend
                ->setStatus('sent')
                ->save();
        }

        $cron->log('[Standalone Push]: done.');
    }

    /**
     * @param string $title
     * @param string $text
     * @param string $cover
     * @param mixed $actionValue
     * @param boolean $forceAppRoute
     * @return Message
     * @throws \Zend_Exception
     */
    public static function buildMessage ($title, $text, $cover, $actionValue, $forceAppRoute = true)
    {
        $message = new Message();
        $message
            ->setIsStandalone(true)
            ->setTitle($title)
            ->setText($text)
            ->setCover($cover)
            ->setSendToAll(false)
            ->setActionValue($actionValue)
            ->setForceAppRoute($forceAppRoute)
            ->setBase64(false);

        return $message;
    }
}