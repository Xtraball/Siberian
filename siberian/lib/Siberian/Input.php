<?php

namespace Siberian;

/**
 * Typed reading of what comes from outside: request parameters, the JSON body of the app, a feed, a form.
 *
 * Why: since PHP 8 arithmetic on a blank string, an array or null is a TypeError or a division by zero, and a cast does
 * not help every case ((int) of an array is 1). Ten fixes of the 5.1.x series each added a cast or a guard at one call
 * site. Read the value through this class instead, once, where it enters:
 *
 *     $offset = Input::int($request->getParam('offset'), 0, 0);        // never negative
 *     $width = Input::float($request->getParam('device_width'), 0.0);
 *     $sort = Input::enum($request->getParam('sort'), ['asc', 'desc'], 'asc');
 *
 * Every method returns the type it names, and the default for anything it cannot read: null, an array, an object, a
 * blank string, text that is not a number. Nothing here throws and nothing raises a notice.
 */
class Input
{
    /**
     * @param mixed $value
     * @param int $default given when $value is not a number
     * @param int|null $min lowest result
     * @param int|null $max highest result
     * @return int
     */
    public static function int($value, $default = 0, $min = null, $max = null)
    {
        $number = self::number($value);
        if ($number === null) {
            $result = (int) $default;
        } elseif ($number >= PHP_INT_MAX) {
            $result = PHP_INT_MAX;
        } elseif ($number <= PHP_INT_MIN) {
            $result = PHP_INT_MIN;
        } else {
            $result = (int) $number; // towards zero, like a cast
        }

        return (int) self::clamp($result, $min, $max);
    }

    /**
     * @param mixed $value
     * @param float $default
     * @param float|null $min
     * @param float|null $max
     * @return float
     */
    public static function float($value, $default = 0.0, $min = null, $max = null)
    {
        $number = self::number($value);

        return (float) self::clamp($number === null ? (float) $default : (float) $number, $min, $max);
    }

    /**
     * A string, trimmed. Only a scalar is text: an array or an object gives the default.
     *
     * @param mixed $value
     * @param string $default
     * @param int|null $maxLength characters
     * @return string
     */
    public static function string($value, $default = '', $maxLength = null)
    {
        if (is_bool($value) || !is_scalar($value) || (is_float($value) && !is_finite($value))) {
            return (string) $default;
        }

        $text = trim((string) $value);
        if ($maxLength !== null && $maxLength >= 0) {
            $text = function_exists('mb_substr') ? mb_substr($text, 0, $maxLength, 'UTF-8') : substr($text, 0, $maxLength);
        }

        return $text;
    }

    /**
     * 1, "1", true, "true", "on", "yes" are true; 0, "0", false, "false", "off", "no", "" are false (any case).
     * Anything else gives the default.
     *
     * @param mixed $value
     * @param bool $default
     * @return bool
     */
    public static function bool($value, $default = false)
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_int($value) || is_float($value)) {
            return $value != 0;
        }
        if (is_string($value)) {
            $word = strtolower(trim($value));
            if (in_array($word, ['1', 'true', 'on', 'yes'], true)) {
                return true;
            }
            if (in_array($word, ['0', 'false', 'off', 'no', ''], true)) {
                return false;
            }
        }

        return (bool) $default;
    }

    /**
     * One of the allowed values (compared as strings, the allowed one is returned), else the default.
     *
     * @param mixed $value
     * @param array $allowed
     * @param mixed $default
     * @return mixed
     */
    public static function enum($value, array $allowed, $default = null)
    {
        if (is_scalar($value) && !is_bool($value) && !(is_float($value) && !is_finite($value))) {
            foreach ($allowed as $candidate) {
                if ((string) $candidate === (string) $value) {
                    return $candidate;
                }
            }
        }

        return $default;
    }

    /**
     * A list of integers from an array or from "1,2,3". What is not a number is dropped, duplicates too.
     *
     * @param mixed $value
     * @param int|null $min numbers below are dropped
     * @return int[]
     */
    public static function intList($value, $min = null)
    {
        if (is_string($value)) {
            $value = explode(',', $value);
        }
        if (!is_array($value)) {
            return [];
        }

        $list = [];
        foreach ($value as $item) {
            $number = self::number($item);
            if ($number === null) {
                continue;
            }
            $int = self::int($number);
            if ($min !== null && $int < $min) {
                continue;
            }
            $list[$int] = $int;
        }

        return array_values($list);
    }

    /**
     * @param mixed $value
     * @return int|float|null the number written in $value, null when there is none
     */
    private static function number($value)
    {
        if (is_int($value)) {
            return $value;
        }
        if (is_float($value)) {
            return is_finite($value) ? $value : null;
        }
        if (is_string($value)) {
            $text = trim($value);
            if ($text !== '' && is_numeric($text)) {
                $number = $text + 0;

                return is_float($number) && !is_finite($number) ? null : $number;
            }
        }

        return null;
    }

    /**
     * @param int|float $value
     * @param int|float|null $min
     * @param int|float|null $max
     * @return int|float
     */
    private static function clamp($value, $min, $max)
    {
        if ($min !== null && $value < $min) {
            $value = $min;
        }
        if ($max !== null && $value > $max) {
            $value = $max;
        }

        return $value;
    }
}
