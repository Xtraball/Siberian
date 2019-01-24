<?php

/**
 * Class Siberian_Version
 */
class Siberian_Version
{
    const TYPE = 'SAE';
    const NAME = 'Single App Edition';
    const VERSION = '4.15.12';
    const NATIVE_VERSION = '9';
    const API_VERSION = '1';

    /**
     * @param $type
     * @return bool
     */
    static function is($type)
    {
        return self::TYPE == strtoupper($type);
    }
}
