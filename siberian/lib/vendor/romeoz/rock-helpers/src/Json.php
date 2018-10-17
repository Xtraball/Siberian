<?php

namespace rock\helpers;

use rock\components\Arrayable;

/**
 * Helper "Json"
 *
 * @package rock\helpers
 */
class Json
{
    /**
     * Validation value is json.
     *
     * @param mixed $value value
     * @return bool
     */
    public static function is($value)
    {
        if (!is_string($value) || empty($value)) {
            return false;
        }
        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * To normalize.
     *
     * @param string $json
     * @return string
     */
    public static function normalize($json)
    {
        $json = str_replace(["\n", "\r"], "", $json);
        $json = preg_replace('/([{,]+)(\s*)([^"]+?)\s*:/', '$1"$3":', $json);

        return preg_replace('/(,)\s*}$/', '}', $json);
    }

    /**
     * Encodes the given value into a JSON string.
     *
     * The method enhances `json_encode()`.
     * @param mixed $value the data to be encoded
     * @param integer $options the encoding options. For more details please refer to
     * @link http://www.php.net/manual/en/function.json-encode.php
     * @return string the encoding result
     */
    public static function encode($value, $options = 0)
    {
        $expressions = [];
        $value = static::processData($value);
        $json = json_encode($value, $options);

        return empty($expressions) ? $json : strtr($json, $expressions);
    }

    /**
     * Converting json to array.
     *
     * @param string $json
     * @param bool $asArray
     * @param bool $throwException
     * @throws JsonException
     * @return array|null
     */
    public static function decode($json, $asArray = true, $throwException = true)
    {
        if (empty($json)) {
            return null;
        }

        $decode = json_decode((string)$json, $asArray);

        if ($throwException === true) {
            switch (json_last_error()) {
                case JSON_ERROR_NONE:
                    break;
                case JSON_ERROR_DEPTH:
                    throw new JsonException('The maximum stack depth has been exceeded.');
                case JSON_ERROR_CTRL_CHAR:
                    throw new JsonException('Control character error, possibly incorrectly encoded.');
                case JSON_ERROR_SYNTAX:
                    throw new JsonException('Syntax error.');
                case JSON_ERROR_STATE_MISMATCH:
                    throw new JsonException('Invalid or malformed JSON.');
                case JSON_ERROR_UTF8:
                    throw new JsonException('Malformed UTF-8 characters, possibly incorrectly encoded.');
                default:
                    throw new JsonException('Unknown JSON decoding error.');
            }
        } else {
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $json;
            }
        }

        return $decode;
    }

    /**
     * Pre-processes the data before sending it to `json_encode()`.
     *
     * @param mixed $data the data to be processed
     * @return mixed the processed data
     */
    protected static function processData($data)
    {
        if (is_object($data)) {
            if ($data instanceof \JsonSerializable) {
                $data = $data->jsonSerialize();
            } elseif ($data instanceof Arrayable) {
                $data = $data->toArray();
            } else {
                $result = [];
                foreach ($data as $name => $value) {
                    $result[$name] = $value;
                }
                $data = $result;
            }

            if ($data === []) {
                return new \stdClass();
            }
        }

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    $data[$key] = static::processData($value);
                }
            }
        }

        return $data;
    }
}