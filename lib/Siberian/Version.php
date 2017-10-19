<?php

class Siberian_Version {

    const TYPE = 'SAE';
    const NAME = 'Single App Edition';
    const VERSION = '4.12.17';
    const NATIVE_VERSION = '4';
    const API_VERSION = 'undefined';

    static function is($type) {
        return self::TYPE == strtoupper($type);
    }
}
