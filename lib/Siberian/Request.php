<?php

class Siberian_Request {

    /**
     * @param $endpoint
     * @param $data
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

        Zend_Debug::dump("[CODE POST] ".$status_code);

        return $result;
    }

    public static function get($endpoint, $data, $cookie_path = null) {

        $request = curl_init();

        $endpoint .= "?".http_build_query($data);

        Zend_Debug::dump($endpoint);

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

        Zend_Debug::dump("[CODE GET] ".$status_code);

        return $result;
    }
}