<?php

/**
 * @category Android Push Notification Service using PHP & MySQL
 * @author Peter Schmalfeldt <manifestinteractive@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link http://code.google.com/p/easyapns/
 */
class Push_Model_Android_Message extends Core_Model_Default {

    /**
     * Android GCM Key
     *
     * @var string
     * @access private
     */
    private $__key;

    /**
     * Android GCM URL
     *
     * @var string
     * @access private
     */
    private $__url = 'https://android.googleapis.com/gcm/send';

    /**
     * Message to push to user
     *
     * @var Push_Model_Message
     * @access protected
     */
    protected $_message;

    /**
     * Constructor.
     *
     * @param array $params
     * @access 	public
     */
    function __construct($params = array()) {
        parent::__construct($params);
        $this->__key = Push_Model_Certificate::getAndroidKey();
    }

    public function setMessage($message) {
        $this->_message = $message;
        return $this;
    }

    public function getMessage() {
        return $this->_message;
    }

    /**
     * Push GCm Messages
     *
     * This gets called automatically by _fetchMessages.  This is what actually deliveres the message.
     *
     * @access public
     */
    public function push() {
        try {

            $device = new Push_Model_Android_Device();
            $app_id = $this->getMessage()->getAppId();

            if ($this->getMessage()->getSendToAll() == 0) {
                $category_message = new Topic_Model_Category_Message();
                $allowed_categories = $category_message->findCategoryByMessageId($this->getMessage()->getId());
            } else {
                $allowed_categories = null;
            }

            //PUSH TO USER ONLY
            $allowed_customers = null;
            if(Push_Model_Message::hasTargetedNotificationsModule()) {
                if ($this->getMessage()->getSendToSpecificCustomer() == 1) {
                    $customer_message = new Push_Model_Customer_Message();
                    $allowed_customers = $customer_message->findCustomersByMessageId($this->getMessage()->getId());
                }
            }

            $devices = $device->findByAppId($app_id,$allowed_categories, $allowed_customers);

            $registration_ids = array();
            foreach ($devices as $device) {
                $registration_ids[] = $device->getRegistrationId();
            }

            if(!empty($registration_ids)) {

                $chunked_registration_ids = array_chunk($registration_ids, 999);
                foreach($chunked_registration_ids as $registration_ids) {
                    
                    $sent = $this->sendMessage($registration_ids);

                    if ($sent) {
                        foreach ($devices as $device) {
                            if($this->getApplication()->useIonicDesign()) {
                                $registration_id = $device->getDeviceUid() ? $device->getDeviceUid() : $device->getRegistrationId();
                            } else {
                                $registration_id = $device->getRegistrationId();

                            }
                            $this->getMessage()->createLog($device, 1, $registration_id);
                        }
                    }
                }
            }
        } catch(Exception $e) {
            $logger = Zend_Registry::get("logger");
            $logger->log(print_r($e,true), Zend_Log::DEBUG);
        }
    }

    /**
     * Send a message to a single device
     * @param type $device
     * @return \Push_Model_Android_Message
     * @throws Exception
     */
    public function sendMessage($registration_ids) {

        try {

            $message = $this->getMessage();

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

            $fields = array(
                'registration_ids' => $registration_ids,
                'data' => array(
                    'time_to_live' => 0,
                    'delay_while_idle' => false,
                    'title' => $message->getTitle(),
                    'message' => $message->getText(),
                    'latitude' => $message->getLatitude(),
                    'longitude' => $message->getLongitude(),
                    'send_until' => $message->getSendUntil() ? $message->getSendUntil() : "0",
                    'radius' => $message->getRadius(),
                    'message_id' => $message->getMessageId(),
                    'cover' => $message->getCoverUrl(),
                    'action_value' => $action_url,
                    "open_webview" => !is_numeric($message->getActionValue())
                ),
            );

            $headers = array(
                'Authorization: key=' . $this->__key,
                'Content-Type: application/json'
            );

            // Open connection
            $ch = curl_init();

            // Set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $this->__url);

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // Disabling SSL Certificate support temporarly
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            curl_setopt($ch, CURLOPT_POSTFIELDS, Zend_Json::encode($fields));

            // Execute post
            $result = curl_exec($ch);

            if(!$result) {
                throw new Exception("");
            }

            Zend_Json::decode($result);

        } catch(Zend_Json_Exception $e) {

            $result = str_replace(PHP_EOL, "", $result);

            if(stripos($result, "Unauthorized") !== false) {
                $message = $this->_("Error 401, not authorized. Please check your GCM key, your sender id and your Google project configuration");
            } else {
                $message = array($e->getMessage(), $result);
                $message = implode(" => ", $message);
            }

            $result = false;

        } catch(Exception $e) {

            $message = array(curl_errno($ch), curl_error($ch));
            $message = implode(" => ", $message);

            $result = false;

            $this->addError();

        }

        curl_close($ch);

        if (!$result) {
//            $this->getMessage()->updateStatus('failed');
            $errors = $this->getErrors();
            if(empty($errors)) $errors = array();
            $errors[] = $message;
            $this->setErrors($errors);
            $sent = false;
        }
        else {
            $sent = true;
        }

        return $sent;

    }


}