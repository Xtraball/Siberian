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
            log_debug("[CODE POST] ".$status_code);
        }


        return $result;
    }

    /**
     * @param $endpoint
     * @param $data
     * @param null $cookie_path
     * @return mixed
     */
    public static function get($endpoint, $data = [], $cookie_path = null) {

        $request = curl_init();

        if(strpos($endpoint, "?") === false && !empty($data)) {
            $endpoint .= "?".http_build_query($data);
        }

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
            log_debug("[CODE GET] " . $status_code);
        }

        return $result;
    }

    /**
     * @param $endpoint
     * @param $data
     * @param null $cookie_path
     * @return mixed
     */
    public static function testStream($endpoint) {

        $request = curl_init();

        # Setting options
        curl_setopt($request, CURLOPT_URL, $endpoint);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($request, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($request, CURLOPT_TIMEOUT, 3);
        curl_setopt($request, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($request, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.78 Safari/537.36');

        # Call
        curl_exec($request);

        $contentType = curl_getinfo($request, CURLINFO_CONTENT_TYPE);

        curl_close($request);

        return $contentType;
    }
}