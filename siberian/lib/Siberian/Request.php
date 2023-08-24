<?php

namespace Siberian;

/**
 * Class Request
 * @package Siberian
 */
class Request
{

    /**
     * @var bool
     */
    public static $debug = false;

    /**
     * @var bool
     */
    public static $statusCode = false;

    /**
     * @param $endpoint
     * @param $data
     * @param null $cookie_path
     * @param null $auth
     * @param null $headers
     * @param array $options
     * @param array $curlOpts
     * @return mixed
     */
    public static function post($endpoint,
                                $data,
                                $cookie_path = null,
                                $auth = null,
                                $headers = null,
                                $options = [],
                                $curlOpts = [])
    {

        $request = curl_init();

        if (self::$debug) {
            curl_setopt($request, CURLOPT_VERBOSE, true);
            $fp = fopen(path('/var/tmp/curl.log'), 'w');
            curl_setopt($request, CURLOPT_STDERR, $fp);
        }

        $timeout = (array_key_exists('timeout', $options)) ? (int) $options['timeout'] : 3;

        # Setting options
        curl_setopt($request, CURLOPT_URL, $endpoint);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($request, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($request, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($request, CURLOPT_POST, true);

        # Settings for sake of Let's Encrypt
        curl_setopt($request, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);

        if ($auth !== null && is_array($auth)) {
            switch ($auth['type']) {
                case 'basic':
                    curl_setopt($request, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                    curl_setopt($request, CURLOPT_USERPWD, $auth['username'] . ':' . $auth['password']);
                    break;
                case 'bearer':
                    curl_setopt($request, CURLOPT_HTTPHEADER, [
                        'Api-Auth-Bearer: Bearer ' . $auth['bearer']
                    ]);
                    break;
            }
        }

        if ($cookie_path !== null) {
            curl_setopt($request, CURLOPT_COOKIEJAR, $cookie_path);
            curl_setopt($request, CURLOPT_COOKIEFILE, $cookie_path);
        }

        if ($headers !== null) {
            curl_setopt($request, CURLOPT_HTTPHEADER, $headers);
        }

        # Query string
        if (array_key_exists('json_body', $options)) {
            curl_setopt($request, CURLOPT_POSTFIELDS, $data);
        } else {
            $query_string = http_build_query($data);
            curl_setopt($request, CURLOPT_POSTFIELDS, $query_string);
        }

        // Adds/Replace custom opts if needed
        curl_setopt_array($request, $curlOpts);

        # Call
        $result = curl_exec($request);
        $status_code = curl_getinfo($request, CURLINFO_HTTP_CODE);

        # Save last status code
        self::$statusCode = $status_code;

        # Closing connection
        curl_close($request);

        if (self::$debug) {
            log_debug("[CODE POST] " . $status_code);
        }


        return $result;
    }

    /**
     * @param $endpoint
     * @param array $data
     * @param null $cookie_path
     * @param null $auth
     * @param null $headers
     * @param array $options
     * @param array $curlOpts
     * @return mixed
     */
    public static function get($endpoint, $data = [], $cookie_path = null, $auth = null, $headers = null, $options = [], $curlOpts = [])
    {

        $request = curl_init();

        if (!empty($data)) {
            // Handling pre-built uris with query
            if (strpos($endpoint, "?") === false) {
                $endpoint .= "?" . http_build_query($data);
            } else {
                $endpoint .= "&" . http_build_query($data);
            }
        }

        # Setting options
        curl_setopt($request, CURLOPT_URL, $endpoint);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($request, CURLOPT_FOLLOWLOCATION, true);

        $timeout = 3;
        if (array_key_exists('timeout', $options)) {
            $timeout = (int) $options['timeout'];
        }

        curl_setopt($request, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($request, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);

        if ($auth !== null && is_array($auth)) {
            switch ($auth['type']) {
                case 'basic':
                    curl_setopt($request, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                    curl_setopt($request, CURLOPT_USERPWD, $auth['username'] . ':' . $auth['password']);
                    break;
                case 'bearer':
                    curl_setopt($request, CURLOPT_HTTPHEADER, [
                        'Api-Auth-Bearer: Bearer ' . $auth['bearer']
                    ]);
                    break;
            }
        }

        if ($cookie_path !== null) {
            curl_setopt($request, CURLOPT_COOKIEJAR, $cookie_path);
            curl_setopt($request, CURLOPT_COOKIEFILE, $cookie_path);
        }

        if ($headers !== null) {
            curl_setopt($request, CURLOPT_HTTPHEADER, $headers);
        }

        // Adds/Replace custom opts if needed
        curl_setopt_array($request, $curlOpts);

        # Call
        $result = curl_exec($request);

        $status_code = curl_getinfo($request, CURLINFO_HTTP_CODE);

        # Save last status code
        self::$statusCode = $status_code;

        # Closing connection
        curl_close($request);

        if (self::$debug) {
            log_debug("[CODE GET] " . $status_code);
        }

        return $result;
    }

    /**
     * @param $endpoint
     * @return mixed
     */
    public static function testStream($endpoint)
    {

        $request = curl_init();

        # Setting options
        curl_setopt($request, CURLOPT_URL, $endpoint);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($request, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($request, CURLOPT_TIMEOUT, 3);
        curl_setopt($request, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
        $headers = [
            'Pragma: no-cache',
            'Accept-Encoding: identity;q=1, *;q=0',
            'Range: bytes=0-',
            'Accept: */*',
            'Connection: keep-alive',
            'Cache-Control: no-cache',
            'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.101 Safari/537.36',
        ];
        curl_setopt($request, CURLOPT_HTTPHEADER, $headers);

        # Call
        curl_exec($request);

        $contentType = curl_getinfo($request, CURLINFO_CONTENT_TYPE);

        curl_close($request);

        return $contentType;
    }
}