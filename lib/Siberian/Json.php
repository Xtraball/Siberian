<?php

/**
 * Class Siberian_Json
 *
 * Id: 
 */
class Siberian_Json extends Zend_Json {

    /**
     * @param mixed $data
     * @param bool|int $options
     * @return array|bool|string
     * @throws Zend_Exception
     */
    public static function encode($data, $options = null, $with_control = false) {
        $logger = Zend_Registry::get("logger");

        if(APPLICATION_ENV === "development") {
            $options |= JSON_PRETTY_PRINT;
        }

        if($with_control) {
            $data = data_to_jsonsafe($data);
        }

        $json = json_encode($data, $options);

        if($json === false && (json_last_error() == JSON_ERROR_UTF8)) {
            /** Trying to convert data to utf8 if array is buggy */
            $logger->err(implode("\n", array("Siberian_Json::encode(), trying to force UTF-8", json_last_error_msg())), "json_error_", false);

            try {
                $data = data_to_utf8($data);
                $json = json_encode($data, $options);
            } catch(Exception $e) {
                /** Catching any exception, the request should always ends ! */
                $json = array(
                    "error" => 1,
                    "message" => "Siberian_Json::encode() UTF-8 failed",
                );

                $logger->err(implode("\n", array("Siberian_Json::encode() UTF-8 failed", $e->getMessage())), "json_error_", false);
            }
        } else if($json === false && (json_last_error() != JSON_ERROR_UTF8)) {
            /** Generic error (not utf-8) */
            $json = array(
                "error" => 1,
                "message" => "Siberian_Json::encode() UTF-8 failed",
            );

            $logger->err(implode("\n", array("Siberian_Json::encode(), unhandeld error", json_last_error_msg())), "json_error_", false);
        }

        return $json;
    }

    /**
     * @param string $json
     * @param int $objectDecodeType
     * @return array|mixed
     */
    public static function decode($json, $objectDecodeType = Zend_Json::TYPE_ARRAY) {
        $result = json_decode($json, true);

        if(is_null($result)) {
            log_err(implode("\n", array("Siberian_Json::decode(), unable to decode json.", json_last_error_msg())));

            /** Set empty result */
            $result = array();
        }

        return $result;
    }
}
