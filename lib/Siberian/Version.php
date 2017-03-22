<?php

class Siberian_Version
{
    const TYPE = 'SAE';
    const NAME = 'Single App Edition';
    const VERSION = '4.9.2';
    const NATIVE_VERSION = '3';

    static function is($type) {
        return self::TYPE == strtoupper($type);
    }
}
