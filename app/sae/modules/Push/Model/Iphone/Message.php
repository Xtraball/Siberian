<?php

/**
 * @category Apple Push Notification Service using PHP & MySQL
 * @package APNS
 * @author Peter Schmalfeldt <manifestinteractive@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link http://code.google.com/p/easyapns/
 */
class Push_Model_Iphone_Message extends Core_Model_Default {

    /**
     * Apples Production APNS Feedback Service
     *
     * @var string
     * @access private
     */
    //private $__feedback_url = 'ssl://feedback.push.apple.com:2196';
    //private $__feedback_development_url = 'ssl://feedback.sandbox.push.apple.com:2196';

    /**
     * Production Certificate Path
     *
     * @var string
     * @access private
     */
    //private $__certificate = '';

    /**
     * Apples APNS Gateway
     *
     * @var string
     * @access private
     */
    //private $__ssl_url = 'ssl://gateway.push.apple.com:2195';
    //private $__ssl_development_url = 'ssl://gateway.sandbox.push.apple.com:2195';

    //public function __construct($datas = array()) {
    //    parent::__construct($datas);
   // }

    /**
     * Message to push to user
     *
     * @var Push_Model_Message
     * @access protected
     */
    //protected $_message;

    /**
     * Stream client to send message
     *
     * @var
     * @access protected
     */
    //protected $_stream_client;


    /*public function setMessage($message) {
        $this->_message = $message;
        if($certificate = Push_Model_Certificate::getiOSCertificat($message->getAppId())) {
            $this->__certificate = Core_Model_Directory::getBasePathTo($certificate);
        }
        return $this;
    }

    public function getMessage() {
        return $this->_message;
    }*/

    /*public function createConnection() {
        $error = false;
        $ctx = stream_context_create();
        stream_context_set_option($ctx, "ssl", "local_cert", $this->__certificate);
        $ssl_url = $this->isProduction() ? $this->__ssl_url : $this->__ssl_development_url;
        $this->_stream_client = stream_socket_client($ssl_url, $error, $errorString, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
        if (!$this->_stream_client) {
            $errors = error_get_last();
            $message =  "";
            if(!empty($errors["type"]) AND !empty($errors["type"])) {
                $message = $errors["message"];
            }
            throw new Exception($message);
        }
    }*/

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

    /**public function sendMessage($device) {

        $message = $this->_formatMessage($device, $this->getMessage());
        $token = $device->getDeviceToken();
        $msg = chr(0) . pack("n", 32) . pack('H*', $token) . pack("n", strlen($message)) . $message;
        $fwrite = fwrite($this->_stream_client, $msg);

        if (!$fwrite) {
            throw new Exception('');
        }

        $this->getMessage()->createLog($device, 1);

        return $this;
    }*/

   /** public function isInsideRadius($lat_a, $lon_a) {

        $radius = $this->getMessage()->getRadius() * 1000;
        $rad = pi() / 180;
        $lat_a = $lat_a * $rad;
        $lat_b = $this->getMessage()->getLatitude() * $rad;
        $lon_a = $lon_a * $rad;
        $lon_b = $this->getMessage()->getLongitude() * $rad;
        $distance = 2 * asin(sqrt(pow(sin(($lat_a-$lat_b)/2) , 2) + cos($lat_a)*cos($lat_b)* pow( sin(($lon_a-$lon_b)/2) , 2)));
        $distance *= 6371000;

        return $distance <= $radius;

    }*/

    /**
     * Fetch APNS Messages
     *
     * This gets called automatically by _pushMessage.  This will check with APNS for any invalid tokens and disable them from receiving further notifications.
     *
     * @param sting $development Which SSL to connect to, Sandbox or Production
     * @access private
     */
    /*private function _checkFeedback() {

        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', $this->__certificate);
        stream_context_set_option($ctx, 'ssl', 'verify_peer', false);
        $feedback_url = $this->isProduction() ? $this->__feedback_url : $this->__feedback_development_url;
        $fp = stream_socket_client($feedback_url, $error, $errorString, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

        if (!$fp) {
            throw new Exception('Unable to connect to get feedback from Apple');
        }
        while ($devcon = fread($fp, 38)) {

            $arr = unpack("H*", $devcon);
            $rawhex = trim(implode("", $arr));
            $token = substr($rawhex, 12, 64);

            if (!empty($token)) {

                $device = new Push_Model_Iphone_Device();
                $device->findByToken($token);
                if($device->getId()) {
                    $device->unregister();
                }
            }

        }
        fclose($fp);
    }*/

    public function _formatMessage($device, $message) {

        $aps = array('aps' => array());

        if($device->getPushAlert() == 'enabled') {
            $aps['aps']['alert'] =  array(
                'body' => $message->getText(),
                'action-loc-key' => $this->_("See")
            );
        }
        if($device->getPushBadge() == 'enabled') {
            $aps['aps']['badge'] = $device->getNotRead() + 1;
        }
        if($device->getPushSound() == 'enabled') {
            $aps['aps']['sound'] = "Submarine.aiff";
        }

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
