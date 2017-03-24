<?php

require_once Core_Model_Directory::getBasePathTo('lib/PHP_GCM/Log.php');
require_once Core_Model_Directory::getBasePathTo('lib/PHP_GCM/Constants.php');
require_once Core_Model_Directory::getBasePathTo('lib/PHP_GCM/Sender.php');
require_once Core_Model_Directory::getBasePathTo('lib/PHP_GCM/Message.php');
require_once Core_Model_Directory::getBasePathTo('lib/PHP_GCM/AggregateResult.php');
require_once Core_Model_Directory::getBasePathTo('lib/PHP_GCM/InvalidRequestException.php');
require_once Core_Model_Directory::getBasePathTo('lib/PHP_GCM/MulticastResult.php');
require_once Core_Model_Directory::getBasePathTo('lib/PHP_GCM/Notification.php');
require_once Core_Model_Directory::getBasePathTo('lib/PHP_GCM/Result.php');

/**
 * Class Push_Model_Android_Message
 */
class Push_Model_Android_Message {

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
    public function __construct(Siberian_Service_Push_Gcm $service_gcm) {
        $this->service_gcm = $service_gcm;
        $this->logger = Zend_Registry::get("logger");
    }

    /**
     * Binder for CA 
     * 
     * @param $path
     */
    public function certificatePath($path) {
        $this->service_gcm->certificatePath($path);
    }

    /**
     * @param Push_Model_Message $message
     */
    public function setMessage(Push_Model_Message $message) {
        $this->message = $message;
    }

    /**
     * @return Push_Model_Message
     */
    public function getMessage() {
        return $this->message;
    }

    /**
     * Push GCm Messages
     *
     * This gets called automatically by _fetchMessages.  This is what actually deliveres the message.
     *
     * @access public
     */
    public function push() {

        $error = null;
        $device = new Push_Model_Android_Device();
        $app_id = $this->getMessage()->getAppId();

        if ($this->getMessage()->getSendToAll() == 0) {
            $category_message = new Topic_Model_Category_Message();
            $allowed_categories = $category_message->findCategoryByMessageId($this->getMessage()->getId());
        } else {
            $allowed_categories = null;
        }

        # Individual push, push to user(s)
        $selected_users = null;
        if(Push_Model_Message::hasIndividualPush()) {
            if ($this->getMessage()->getSendToSpecificCustomer() == 1) {
                $customer_message = new Push_Model_Customer_Message();
                $selected_users = $customer_message->findCustomersByMessageId($this->getMessage()->getId());
            }
        }

        $devices = $device->findByAppId($app_id, $allowed_categories, $selected_users);

        $gcm_message = $this->buildMessage();

        $app = new Application_Model_Application();
        $app->find($app_id);
        $app_use_ionic = ($app->useIonicDesign());

        $device_by_token = array();
        $registration_tokens = array();
        foreach ($devices as $device) {
            $device_by_token[$device->getRegistrationId()] = $device;
            $registration_tokens[] = $device->getRegistrationId();
        }

        if(empty($registration_tokens)) {
            $this->service_gcm->logger->log("No Android devices registered, done.");
            return;
        }

        # Send message.
        try {
            $aggregate_result = $this->service_gcm->send($gcm_message, $registration_tokens);

            foreach($aggregate_result->getResults() as $result) {

                try {

                    # Fetch the device
                    $registration_id = $result->getRegistrationId();
                    if(isset($device_by_token[$registration_id])) {
                        $device = $device_by_token[$registration_id];
                    } else {
                        continue;
                    }

                    $messageId = $result->getMessageId();
                    $errorCode = $result->getErrorCode();
                    if(!empty($messageId)) {

                        if($app_use_ionic) {
                            $registration_id = $device->getDeviceUid() ? $device->getDeviceUid() : $device->getRegistrationId();
                        } else {
                            $registration_id = $device->getRegistrationId();
                        }

                        /** Very important code, this links push message to user/device */
                        $this->getMessage()->createLog($device, 1, $registration_id);

                    } else if(!empty($errorCode)) {

                        # Handle error
                        $error_count = $device->getErrorCount();
                        if($error_count >= 2) {
                            # Remove device from list
                            $device->delete();

                            $msg = sprintf("#810-01: Android Device with ID: %s, Token: %s, removed after 2 failed push.", $device->getId(), $registration_id);
                            $this->logger->info($msg, "push_android", false);
                        } else {
                            $device->setErrorCount(++$error_count)->save();

                            $msg = sprintf("#810-02: Android Device with ID: %s, Token: %s, failed push ! Errors count: %s.", $device->getId(), $registration_id, $error_count);
                            $this->logger->info($msg, "push_android", false);
                        }

                    }
                } catch(Exception $e) {
                    $msg = sprintf("#810-06: Android Device with ID: %s, Token: %s failed ! Error message: %s.", $device->getId(), $registration_id, $e->getMessage());
                    $this->logger->info($msg, "push_android", false);
                }
            }

        } catch (InvalidArgumentException $e) { # $deviceRegistrationId was null
            $error = sprintf("#810-03: PushGCM InvalidArgumentException with error: %s.", $e->getMessage());
        } catch (PHP_GCM\InvalidRequestException $e) { # server returned HTTP code other than 200 or 503
            $error = sprintf("#810-04: PushGCM InvalidRequestException with error: %s.", $e->getMessage());
        } catch (Exception $e) { # message could not be sent
            $error = sprintf("#810-05: PushGCM Exception with error: %s.", $e->getMessage());
        }

        if(!empty($error)) {
            $this->service_gcm->logger->log($error);

            # Throw exception up to notify the push failed
            throw new Siberian_Exception($error);
        }

    }

    /**
     * @return Siberian_Service_Push_Gcm_Message
     */
    public function buildMessage() {
        $gcm_message = new Siberian_Service_Push_Gcm_Message();

        $message = $this->getMessage();

        $application = new Application_Model_Application();
        $application->find($message->getAppId());

        if(is_numeric($message->getActionValue())) {
            $option_value = new Application_Model_Option_Value();
            $option_value->find($message->getActionValue());

            $action_url = sprintf("/%s/%sindex/value_id/%s", $application->getKey(), $option_value->getMobileUri(), $option_value->getId());
        } else {
            $action_url = $message->getActionValue();
        }

        $gcm_message
            ->setMessageId($message->getMessageId())
            ->setTitle($message->getTitle())
            ->setMessage($message->getText())
            ->setGeolocation($message->getLatitude(), $message->getLongitude(), $message->getRadius())
            ->setCover($message->getCoverUrl(), $message->getData("base_url").$message->getCoverUrl(), $message->getText())
            ->setDelayWithIdle(false)
            ->setTimeToLive(0)
            ->setSendUntil($message->getSendUntil() ? $message->getSendUntil() : "0")
            ->setActionValue($action_url)
            ->setOpenWebview(!is_numeric($message->getActionValue()))
        ;

        # Priority to custom image
        $custom_image = $message->getCustomImage();
        $path_custom_image = Core_Model_Directory::getBasePathTo("/images/application".$custom_image);
        if(is_readable($path_custom_image) && !is_dir($path_custom_image)) {
            $gcm_message->setImage($message->getData("base_url")."/images/application".$custom_image);
        } else {
            # Default application image
            $application_image = $application->getAndroidPushImage();
            if(!empty($application_image)) {
                $gcm_message->setImage($message->getData("base_url")."/images/application".$application_image);
            }
        }

        if($application->useIonicDesign() && ($message->getLongitude() && $message->getLatitude())) {
            $gcm_message->contentAvailable(true);
        }

        return $gcm_message;
    }


}