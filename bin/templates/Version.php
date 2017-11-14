<?php
/**
 * Class Siberian_Version
 */
class Siberian_Version {
    const TYPE = '%TYPE%';
    const NAME = '%NAME%';
    const VERSION = '%VERSION%';
    const NATIVE_VERSION = '%NATIVE_VERSION%';
    const API_VERSION = '%API_VERSION%';

    static function is($type) {
        return self::TYPE == strtoupper($type);
    }
}
