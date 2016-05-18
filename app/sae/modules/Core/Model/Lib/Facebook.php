<?php

require_once 'Connect/facebook.php';

class Core_Model_Lib_Facebook extends Core_Model_Default {

    protected $_facebook;

    public function __construct($params = array()) {
        parent::__construct($params);

        $config = array(
            'appId' => self::getAppId(),
            'secret' => self::getSecretKey(),
        );

        $this->_facebook = new Facebook($config);

        return $this;
    }

    public static function getAppId() {
        return Api_Model_Key::findKeysFor('facebook')->getAppId();
    }

    public static function getSecretKey() {
        return Api_Model_Key::findKeysFor('facebook')->getSecretKey();
    }

    public static function getOrRefreshToken($access_token) {
        /** Refresh the token life. */
        $facebook_app_id = self::getAppId();
        $facebook_secret = self::getSecretKey();

        $token_url = "https://graph.facebook.com/oauth/access_token?grant_type=fb_exchange_token&client_id={$facebook_app_id}&client_secret={$facebook_secret}&fb_exchange_token={$access_token}";
        $response = file_get_contents($token_url);

        $results = array();
        if(preg_match("/access_token=(.*)&/", $response, $results)){
            $access_token = $results[1];
        }
        else {
            return false;
        }

        return $access_token;
    }

}