<?php

class Siberian_Version
{
    const TYPE = 'SAE';
    const NAME = 'Single App Edition';
    const VERSION = '4.8.12.6';
    const NATIVE_VERSION = '3';

    static function is($type) {
        return self::TYPE == strtoupper($type);
    }
}
