<?php

/**
 * Class Siberian_Session_Namespace
 */
class Siberian_Session_Namespace extends Zend_Session_Namespace
{
    /**
     * @var string
     */
    const SERIALIZE_KEY = "#SERIALIZED#";

    /**
     * @param string $name
     * @return bool|mixed|string
     * @throws Zend_Session_Exception
     */
    public function & __get($name)
    {
        $value = parent::__get($name);

        if (strpos($value, self::SERIALIZE_KEY) === 0) {
            $value = substr($value, strlen(self::SERIALIZE_KEY));
            $value = unserialize($value);
        }

        return $value;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return true|void
     * @throws Zend_Session_Exception
     */
    public function __set($name, $value)
    {
        if (is_object($value)) {
            $value = "#SERIALIZED#" . serialize($value);
        }
        parent::__set($name, $value);
    }

}
