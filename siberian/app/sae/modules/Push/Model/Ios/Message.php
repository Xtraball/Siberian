<?php

use Siberian\Hook;

/**
 * Class Push_Model_Ios_Message
 */
class Push_Model_Ios_Message
{

    /**
     * @var null|Siberian_Service_Push_Apns
     */
    private $service_apns = null;

    /**
     * @var null|Push_Model_Message
     */
    private $message = null;

    /**
     * @var null|Siberian_Log
     */
    private $logger = null;

    /**
     * Push_Model_Ios_Message constructor.
     * @param Siberian_Service_Push_Apns $service_apns
     */
    public function __construct(Siberian_Service_Push_Apns $service_apns)
    {
        $this->service_apns = $service_apns;
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
     * Push message to selected devices
     */
    public function push()
    {
        $devices = [];
        $message = $this->getMessage();
        $appId = $message->getAppId();
        $app = (new Application_Model_Application())->find($appId);
        $bundleId = $app->getBundleId();

        // New standalone push
        if ($message->getIsStandalone() === true) {
            $deviceObj = (new Push_Model_Iphone_Device())
                ->find($message->getToken(), 'device_token');
            if ($deviceObj->getPushAlert() === 'enabled') {
                $devices = [$deviceObj];
                $this->service_apns->addMessage($message, $bundleId, $devices);
            }
        } else {
            if ($message->getSendToAll() == 0) {
                $category_message = new Topic_Model_Category_Message();
                $allowed_categories = $category_message->findCategoryByMessageId($message->getId());
            } else {
                $allowed_categories = null;
            }

            # Individual push, push to user(s)
            $selected_users = null;
            if (Push_Model_Message::hasIndividualPush() &&
                $message->getSendToSpecificCustomer() == 1) {
                $customer_message = new Push_Model_Customer_Message();
                $selected_users = $customer_message->findCustomersByMessageId($message->getId());
            }

            $devices = (new Push_Model_Iphone_Device())->findByAppId($appId, $allowed_categories, $selected_users);
            $this->service_apns->addMessage($message, $bundleId, $devices);
        }

        # Send all queued messages
        $this->service_apns->sendAll();

        # Fetch errors
        $responses = $this->service_apns->responses;
        $deviceUidErrors = [];
        foreach ($responses as $response) {
            $badDevice = \Dashi\Apns2\Response::REASON_BAD_DEVICE_TOKEN;
            if ($response->reason === $badDevice) {
                $deviceUidErrors[$response->deviceId] = strtolower($badDevice);
            }
        }

        # Create logs & Dump errors into log.
        foreach ($devices as $_device) {
            try {
                $uid = strtolower($_device->getDeviceUid());
                if (!array_key_exists($uid, $deviceUidErrors)) {
                    # Push ok, so create log
                    $this->message->createLog($_device, 1);
                } else {
                    # Handle error
                    $errorCount = $_device->getErrorCount();
                    if ($errorCount >= 2) {
                        # Remove device from list
                        $msg = sprintf("#800-01: iOS Device with ID: %s, Token: %s, removed after 3 failed push.", $_device->getId(), $_device->getDeviceToken());

                        Hook::trigger('push.ios.delete_token', [
                            'device' => $_device
                        ]);

                        $_device->delete();
                        $this->logger->info($msg, "push_ios", false);
                    } else {
                        $_device->setErrorCount(++$errorCount)->save();

                        $msg = sprintf("#800-02: iOS Device with ID: %s, Token: %s, failed push ! Errors count: %s.", $_device->getId(), $_device->getDeviceToken(), $errorCount);
                        $this->logger->info($msg, 'push_ios', false);
                    }

                    if (isset($msg)) {
                        $this->service_apns->_log($msg);
                    }
                }

            } catch (\Exception $e) {
                $msg = sprintf("#800-03: iOS Device with ID: %s, Token: %s, failed ! Error message: %s.", $_device->getId(), $_device->getDeviceToken(), $e->getMessage());
                $this->logger->info($msg, 'push_ios', false);
            }
        }

    }
}
