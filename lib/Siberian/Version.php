<?php

class Siberian_Version
{
    const TYPE = 'SAE';
    const NAME = 'Single App Edition';
    const VERSION = '4.10.0';
    const NATIVE_VERSION = '4';

    static function is($type) {
        return self::TYPE == strtoupper($type);
    }
}
