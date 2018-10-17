<?php

namespace rock\helpers;

class Helper implements SerializeInterface
{
    /**
     * Get value.
     *
     * ```php
     * Helper::getValue($array['foo']);
     * ```
     *
     * @param mixed $value
     * @param mixed $default if value is empty
     * @param bool $isset check as isset
     * @return null
     */
    public static function getValue(&$value, $default = null, $isset = false)
    {
        if ($isset) {
            return isset($value) ? $value : $default;
        }
        return $value ?: $default;
    }

    /**
     * If value don't empty then update via callable.
     *
     * @param mixed $value
     * @param callable $callback
     * @param mixed $default
     * @param bool $isset check as isset
     * @return mixed|null
     */
    public static function update(&$value, callable $callback, $default = null, $isset = false)
    {
        if ($isset) {
            return isset($value) ? call_user_func($callback, $value) : $default;
        }
        return $value ? call_user_func($callback, $value) : $default;
    }

    /**
     * Conversion to type.
     *
     * @param mixed $value value
     * @return mixed
     */
    public static function toType($value)
    {
        if (!is_string($value)) {
            return $value;
        }
        if ($value === 'null') {
            $value = null;
        } elseif (is_numeric($value)) {
            $value = NumericHelper::toNumeric($value);
        } elseif ($value === 'false') {
            $value = false;
        } elseif ($value === 'true') {
            $value = true;
        }

        return $value;
    }

    /**
     * Clear value by type.
     *
     * @param mixed $value value
     * @return mixed
     */
    public static function clearByType($value)
    {
        if (is_null($value)) {
            return null;
        } elseif (is_array($value)) {
            return [];
        } elseif (is_string($value)) {
            return '';
        } elseif (is_int($value)) {
            return 0;
        } elseif (is_float($value)) {
            return 0.0;
        } elseif (is_object($value) && !$value instanceof \Closure) {
            if (method_exists($value, 'reset')) {
                $value->reset();
                return $value;
            }
            $class = get_class($value);
            return new $class;
        }

        return $value;
    }
}