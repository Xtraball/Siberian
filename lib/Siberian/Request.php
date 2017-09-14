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
        $headers = array(
            'Pragma: no-cache',
            'Accept-Encoding: identity;q=1, *;q=0',
            'Range: bytes=0-',
            'Accept: */*',
            'Connection: keep-alive',
            'Cache-Control: no-cache',
            'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.101 Safari/537.36',
        );
        curl_setopt($request, CURLOPT_HTTPHEADER, $headers);

        # Call
        curl_exec($request);

        $contentType = curl_getinfo($request, CURLINFO_CONTENT_TYPE);

        curl_close($request);

        return $contentType;
    }
}