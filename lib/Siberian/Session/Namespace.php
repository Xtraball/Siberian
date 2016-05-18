<?php

class Siberian_Session_Namespace extends Zend_Session_Namespace
{
    const SERIALIZE_KEY = "#SERIALIZED#";

    public function & __get($name)
    {
        $value = parent::__get($name);

        if(strpos($value, self::SERIALIZE_KEY) === 0) {
            $value = substr($value, strlen(self::SERIALIZE_KEY));
            $value = unserialize($value);
        }

        return $value;
    }

    public function __set($name, $value)
    {
        if(is_object($value)) {
            $value = "#SERIALIZED#".serialize($value);
        }
        parent::__set($name, $value);
    }

}
