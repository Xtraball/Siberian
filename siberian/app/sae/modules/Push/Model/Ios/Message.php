<?php

/**
 * Class Push_Model_Ios_Message
 */
class Push_Model_Ios_Message {

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
    public function __construct(Siberian_Service_Push_Apns $service_apns) {
        $this->service_apns = $service_apns;
        $this->logger = Zend_Registry::get("logger");
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
     * Push message to selected devices
     */
    public function push() {
        $device = new Push_Model_Iphone_Device();
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
        foreach($devices as $device) {
            $this->service_apns->addMessage($this->message, $device);
        }

        # Send all queued messages
        $this->service_apns->sendAll();

        # Fetch errors
        $apns_errors = $this->service_apns->getErrors();
        $device_errors = array();
        foreach($apns_errors as $error) {
            $err_string = (isset($error["ERRORS"]) && isset($error["ERRORS"][0])) ? $error["ERRORS"][0]["statusMessage"] : "Unknown APNS Push Error";
            $message = $error["MESSAGE"];
            $custom_identifier = $message->getCustomIdentifier();
            $tmp = explode("-", $custom_identifier);
            $device_id = $tmp[1];

            $device_errors[$device_id] = $err_string;
        }

        # Create logs & Dump errors into log.
        foreach($devices as $device) {
            try {

                if(!array_key_exists($device->getId(), $device_errors)) {
                    # Push ok, so create log
                    $this->message->createLog($device, 1);
                } else {
                    # Handle error
                    $error_count = $device->getErrorCount();
                    if($error_count >= 2) {
                        # Remove device from list
                        $msg = sprintf("#800-01: iOS Device with ID: %s, Token: %s, removed after 3 failed push.", $device->getId(), $device->getDeviceToken());

                        $device->delete();
                        $this->logger->info($msg, "push_ios", false);
                    } else {
                        $device->setErrorCount(++$error_count)->save();

                        $msg = sprintf("#800-02: iOS Device with ID: %s, Token: %s, failed push ! Errors count: %s.", $device->getId(), $device->getDeviceToken(), $error_count);
                        $this->logger->info($msg, "push_ios", false);
                    }

                    if(isset($msg)) {
                        $this->service_apns->_log($msg);
                    }
                }

            } catch(Exception $e) {
                $msg = sprintf("#800-03: iOS Device with ID: %s, Token: %s, failed ! Error message: %s.", $device->getId(), $device->getDeviceToken(), $e->getMessage());
                $this->logger->info($msg, "push_ios", false);
            }
        }

    }
}