<?php

namespace rock\helpers;

/**
 * Helper "Serialize"
 *
 * @package rock\helpers
 */
class Serialize implements SerializeInterface
{
    /**
     * Serialize.
     *
     * @param array $value
     * @param int $serializer
     * @param int $options constants by JSON
     * @return string
     */
    public static function serialize(array $value, $serializer = self::SERIALIZE_PHP, $options = 0)
    {
        return $serializer === self::SERIALIZE_PHP ? serialize($value) : Json::encode($value, $options);
    }

    /**
     * @param mixed $value
     * @param bool $throwException
     * @throws SerializeException
     * @return mixed
     */
    public static function unserialize($value, $throwException = true)
    {
        if ($throwException === false) {
            if (!is_string($value)) {
                return $value;
            }
        }

        if (static::is($value)) {
            return unserialize($value);
        } elseif (Json::is($value)) {
            return Json::decode($value);
        }
        if ($throwException == true) {
            throw new SerializeException(SerializeException::NOT_SERIALIZE);
        }

        return $value;
    }

    public static function unserializeRecursive($value)
    {
        if (empty($value)) {
            return $value;
        }
        if (is_array($value)) {
            return ArrayHelper::map(
                $value,
                function ($value) {
                    return static::unserializeRecursive($value);
                },
                true
            );
        }
        return static::unserialize($value, false);
    }

    /**
     * Validation is serialized.
     *
     * @param string $value
     * @return bool
     */
    public static function is($value)
    {
        return is_string($value) && ($value === 'b:0;' || @unserialize($value) !== false);
    }
}