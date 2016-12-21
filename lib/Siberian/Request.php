<?php

class Siberian_Request {

    /**
     * @var bool
     */
    public static $debug = false;

    /**
     * @param $endpoint
     * @param $data
     * @param null $cookie_path
     * @return mixed
     */
    public static function post($endpoint, $data, $cookie_path = null) {

        $request = curl_init();

        # Setting options
        curl_setopt($request, CURLOPT_URL, $endpoint);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($request, CURLOPT_TIMEOUT, 3);
        curl_setopt($request, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($request, CURLOPT_POST, true);
        curl_setopt($request, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);

        if($cookie_path != null) {
            curl_setopt($request, CURLOPT_COOKIEJAR, $cookie_path);
            curl_setopt($request, CURLOPT_COOKIEFILE, $cookie_path);
        }

        # Query string
        $query_string = http_build_query($data);
        curl_setopt($request, CURLOPT_POSTFIELDS, $query_string);

        # Call
        $result = curl_exec($request);
        $status_code = curl_getinfo($request, CURLINFO_HTTP_CODE);

        # Closing connection
        curl_close($request);

        if(self::$debug) {
            Zend_Debug::dump("[CODE POST] ".$status_code);
        }


        return $result;
    }

    /**
     * @param $endpoint
     * @param $data
     * @param null $cookie_path
     * @return mixed
     */
    public static function get($endpoint, $data, $cookie_path = null) {

        $request = curl_init();

        $endpoint .= "?".http_build_query($data);

        # Setting options
        curl_setopt($request, CURLOPT_URL, $endpoint);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($request, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($request, CURLOPT_TIMEOUT, 3);
        curl_setopt($request, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);

        if($cookie_path != null) {
            curl_setopt($request, CURLOPT_COOKIEJAR, $cookie_path);
            curl_setopt($request, CURLOPT_COOKIEFILE, $cookie_path);
        }

        # Call
        $result = curl_exec($request);
        $status_code = curl_getinfo($request, CURLINFO_HTTP_CODE);

        # Closing connection
        curl_close($request);

        if(self::$debug) {
            Zend_Debug::dump("[CODE GET] " . $status_code);
        }

        return $result;
    }
}