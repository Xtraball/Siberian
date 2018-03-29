<?php
/**
 * Class Siberian_Version
 */
class Siberian_Version {
    const TYPE = 'SAE';
    const NAME = 'Single App Edition';
    const VERSION = '4.13.10';
    const NATIVE_VERSION = '4';
    const API_VERSION = '1';

    static function is($type) {
        return self::TYPE == strtoupper($type);
    }
}
