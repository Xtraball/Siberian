<?php

class Importer_Model_FacebookParser extends Core_Model_Default {

    public function __construct($params = array()) {
        parent::__construct($params);

    }

    public function getToken() {
        $facebookAPP_ID     = Api_Model_Key::findKeysFor('facebook')->getAppId();
        $facebookAPP_SECRET = Api_Model_Key::findKeysFor('facebook')->getSecretKey();

        if(!$facebookAPP_SECRET OR !$facebookAPP_ID) {
            return false;
        } else {
            $url = self::buildUrl("oauth/access_token", array(
                "grant_type" => "client_credentials",
                "client_id" => $facebookAPP_ID,
                "client_secret" => $facebookAPP_SECRET,
            ));
            $content = Zend_Json::decode(self::__curl_get($url));

            return $content["access_token"] ? $content["access_token"] : false;
        }

    }

    public function parsePage($page_id, $token, $fields = null) {

        $fields = $fields ? $fields : "id,hours,location,phone,emails,events,general_info,contact_address,description,current_location,about,name,genre,cover,website,link";
        $url = self::buildUrl($page_id, array(
            "access_token" => $token,
            "fields" => $fields
        ));

        $response = file_get_contents($url);
        $response = Zend_Json::decode($response);

        return $response;
    }

    public function parsePageAlbums($page_id, $token) {

        $url = self::buildUrl($page_id."/albums", array(
            "access_token" => $token
        ));

        $response = file_get_contents($url);
        $response = Zend_Json::decode($response);

        return $response;
    }

    private static function buildUrl($path, $params = array()) {
        return sprintf("%s/%s?%s", "https://graph.facebook.com/v2.9", $path, http_build_query($params));
    }

    private static function __curl_get($url) {
        $request = curl_init();
        # Setting options
        curl_setopt($request, CURLOPT_URL, $url);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($request, CURLOPT_TIMEOUT, 3);
        # Call
        $result = curl_exec($request);
        # Closing connection
        curl_close($request);

        return $result;
    }
}
