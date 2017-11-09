<?php

/**
 * @category Apple Push Notification Service using PHP & MySQL
 * @package APNS
 * @author Peter Schmalfeldt <manifestinteractive@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link http://code.google.com/p/easyapns/
 */
class Push_Model_Iphone_Message extends Core_Model_Default {

    public function push() {
        
        
        $device = new Push_Model_Iphone_Device();
        $app_id = $this->getMessage()->getAppId();

        if ($this->getMessage()->getSendToAll() == 0) {
            $category_message = new Topic_Model_Category_Message();
            $allowed_categories = $category_message->findCategoryByMessageId($this->getMessage()->getId());
        } else {
            $allowed_categories = null;
        }

        //PUSH TO USER ONLY
        $allowed_customers = null;
        if(Push_Model_Message::hasIndividualPush()) {
            if ($this->getMessage()->getSendToSpecificCustomer() == 1) {
                $customer_message = new Push_Model_Customer_Message();
                $allowed_customers = $customer_message->findCustomersByMessageId($this->getMessage()->getId());
            }
        }

        $devices = $device->findByAppId($app_id, $allowed_categories, $allowed_customers);
        $errors = array();

        $error = false;
        foreach($devices as $device) {

            try {

                $this->sendMessage($device);

            } catch(Exception $e) {

                $errors[$device->getId()] = "Device {$device->getId()} -> {$e->getMessage()}";

            }

        }

        fclose($this->_stream_client);

        try {
            $this->_checkFeedback();
        } catch(Exception $e) {
            $errors[] = $e->getMessage();
        }

        $this->setErrors($errors);

        return $this;

    }

    public function _formatMessage($device, $message) {

        $aps = array('aps' => array());

        $aps['aps']['alert'] =  array(
            'body' => $message->getText(),
            'action-loc-key' => $this->_("See")
        );
        $aps['aps']['badge'] = $device->getNotRead() + 1;
        $aps['aps']['sound'] = "Submarine.aiff";

        // Push Geolocated //
        if($message->getLongitude() && $message->getLatitude()) {
            $aps = array(
                'aps' => array(
                    'message_id' => $message->getId(),
                    'content-available' => 1,
                    'sound' => '',
                    'latitude' => $message->getLatitude(),
                    'longitude' => $message->getLongitude(),
                    'send_until' => $message->getSendUntil(),
                    'radius' => $message->getRadius(),
                    'user_info' => $aps['aps']
                )
            );
        }

        if(is_numeric($message->getActionValue())) {
            $option_value = new Application_Model_Option_Value();
            $option_value->find($message->getActionValue());

            $application = new Application_Model_Application();
            $application->find($message->getAppId());

            $action_url = $application->getPath($option_value->getMobileUri() . "index", array('value_id' => $option_value->getId()), false);
            $action_url = "/" . $application->getKey() . $action_url;
        } else {
            $action_url = $message->getActionValue();
        }

        $aps['aps']['alert']['cover'] = $message->getCoverUrl();
        $aps['aps']['alert']['action_value'] = $action_url;
        $aps['aps']['alert']['open_webview'] = !is_numeric($message->getActionValue());

        return Zend_Json::encode($aps);

    }

}
