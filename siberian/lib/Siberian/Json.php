<?php

namespace Siberian;

use \Zend_Json;

/**
 * Class Siberian_Json
 *
 * Id: 
 */
class Json extends Zend_Json
{
    /**
     * @param mixed $data
     * @param null $options
     * @param bool $withControl
     * @return string
     */
    public static function encode($data, $options = 0, $withControl = false)
    {
        if (APPLICATION_ENV === "development") {
            $options |= JSON_PRETTY_PRINT;
        }

        if ($withControl) {
            $data = data_to_jsonsafe($data);
        }

        $json = json_encode($data, $options);

        if ($json === false && (json_last_error() == JSON_ERROR_UTF8)) {
            // Trying to convert data to utf8 if array is buggy!
            log_warn(implode_polyfill(
                "\n",
                ["Siberian_Json::encode(), trying to force UTF-8", json_last_error_msg()]),
                "json_error_",
                false);

            try {
                $data = data_to_utf8($data);
                $json = json_encode($data, $options);
            } catch (Exception $e) {
                // Catching any exception, the request should always ends!
                $json = json_encode([
                    "error" => 1,
                    "message" => "Siberian_Json::encode() UTF-8 failed",
                ]);

                log_warn(implode_polyfill(
                    "\n",
                    ["Siberian_Json::encode() UTF-8 failed", $e->getMessage()]),
                    "json_error_",
                    false);
            }
        } else if ($json === false && (json_last_error() != JSON_ERROR_UTF8)) {
            // Generic error (not utf-8)!
            $json = json_encode([
                "error" => 1,
                "message" => "Siberian_Json::encode() UTF-8 failed",
            ]);

            log_warn(implode_polyfill(
                "\n",
                ["Siberian_Json::encode(), unhandeld error", json_last_error_msg()]),
                "json_error_",
                false);
        }

        return $json;
    }

    /**
     * @param string $json
     * @param int $objectDecodeType
     * @return array|mixed
     */
    public static function decode($json, $objectDecodeType = Zend_Json::TYPE_ARRAY)
    {
        if (empty($json)) {
            return [];
        }

        $result = json_decode($json, true);

        if (is_null($result)) {
            log_warn(implode_polyfill(
                "\n",
                ['Siberian_Json::decode(), unable to decode json.', json_last_error_msg()]));

            // Set empty result!
            $result = [];
        }

        return $result;
    }

    /**
     * True when $payload is array-shaped and explicitly flags itself as an error response.
     *
     * Callers like Core_Controller_Default_Abstract::_sendJson() used to do
     * `array_key_exists("error", $payload) && $payload["error"] == true` directly, assuming
     * $payload is always an array. It is not always one: _sendHtml() forwards a raw string
     * into _sendJson() as $payload, and PHP 8 turns array_key_exists() on a non-array into an
     * uncaught TypeError instead of the old "always false" behaviour.
     *
     * @param mixed $payload
     * @return bool
     */
    public static function isErrorResponse($payload)
    {
        return is_array($payload) && array_key_exists('error', $payload) && $payload['error'] == true;
    }
}
