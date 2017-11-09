<?php

require_once Core_Model_Directory::getBasePathTo('lib/ApnsPHP/Autoload.php');

class Siberian_Service_Push_Apns extends ApnsPHP_Push {

    const ROOT_CA = "/var/apps/certificates/root_ca.pem";

    public function __construct($nEnvironment, $sProviderCertificateFile) {
        if(is_null($nEnvironment)) {
            $nEnvironment = (APPLICATION_ENV == "production") ? ApnsPHP_Push::ENVIRONMENT_PRODUCTION : ApnsPHP_Push::ENVIRONMENT_SANDBOX;
        }

        parent::__construct($nEnvironment, $sProviderCertificateFile);

        $root_ca = Core_Model_Directory::getBasePathTo(self::ROOT_CA);
        $this->setRootCertificationAuthority($root_ca);

        $this->connect();
    }

    /**
     * @param $push_message
     * @param $device
     * @throws ApnsPHP_Message_Exception
     */
    public function addMessage($push_message, $device) {

        $message = new Siberian_Service_Push_Apns_Message($device->getDeviceToken());

        # Custom identifier for further feedback
        $message->setCustomIdentifier(sprintf("device_id-%s", $device->getId()));

        # Badge count
        $message->setBadge($device->getNotRead() + 1);

        # Message
        $message->setTitle($push_message->getTitle());
        $message->setText($push_message->getText());
        $message->setActionLocKey(__("See"));

        # Sound
        $message->setSound("Submarine.aiff");

        # Cover
        $message->setCover($push_message->getCoverUrl());

        # Cover
        $message->setValueId($push_message->getValueId());

        # Action
        if(is_numeric($push_message->getActionValue())) {
            $option_value = new Application_Model_Option_Value();
            $option_value->find($push_message->getActionValue());

            $application = new Application_Model_Application();
            $application->find($push_message->getAppId());

            $action_url = sprintf("/%s/%sindex/value_id/%s", $application->getKey(), $option_value->getMobileUri(), $option_value->getId());
        } else {
            $action_url = $push_message->getActionValue();
        }
        $message->setActionValue($action_url);

        # Whether the action should open a webview
        $message->setOpenWebView((!is_numeric($push_message->getActionValue())));
        $message->setMessageId($push_message->getId());

        # Geolocation @TODO finish geolocation push when merged
        if($push_message->getLongitude() && $push_message->getLatitude()) {
            # Fetch current message as array.
            $payload = $message->_getPayload();
            $user_info = $payload[Siberian_Service_Push_Apns_Message::APPLE_RESERVED_NAMESPACE];

            # Build new message
            $geolocated_message = new Siberian_Service_Push_Apns_Message();
            $geolocated_message->setContentAvailable(true);
            $geolocated_message->setSound('');
            $geolocated_message->setGeolocation($push_message->getLatitude(), $push_message->getLongitude(), $push_message->getRadius());
            $geolocated_message->setSendUntil($push_message->getSendUntil() ? $push_message->getSendUntil() : null);
            $geolocated_message->setUserInfo($user_info);
            $geolocated_message->setMessageId($push_message->getId());

            # Set the empty alert (silent)
            $geolocated_message->setCover("");
            $geolocated_message->setActionValue($action_url);
            $geolocated_message->setOpenWebView((!is_numeric($push_message->getActionValue())));

            # Add recipient
            $geolocated_message->addRecipient($device->getDeviceToken());

            # Custom identifier for further feedback
            $geolocated_message->setCustomIdentifier(sprintf("device_id-%s", $device->getId()));

            $message = $geolocated_message;
        }
        
        # Add message to the message queue
        $this->add($message);
    }

    public function sendAll() {
        $this->send();
        $this->disconnect();
    }

    /**
     * @param $sMessage
     */
    public function _log($sMessage) {
        parent::_log($sMessage);
    }
}
