<?php

namespace Siberian;

/**
 * Class Version
 * @package Siberian
 *
 * @replay 4.16.1
 */
class Version
{
    const TYPE = 'SAE';
    const NAME = 'Single App Edition';
    const VERSION = '4.16.1';
    const NATIVE_VERSION = '10';
    const API_VERSION = '4';

    /**
     * @param string|array $type
     * @return bool
     */
    static function is($type)
    {
        if (is_array($type)) {
            foreach ($type as $t) {
                if (self::TYPE == strtoupper($t)) {
                    return true;
                }
            }
        }
        return self::TYPE == strtoupper($type);
    }
}