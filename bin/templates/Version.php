<?php

namespace Siberian;

/**
 * Class \Siberian\Version
 *
 * @ignore
 */
class Version
{
    const TYPE = '%TYPE%';
    const NAME = '%NAME%';
    const VERSION = '%VERSION%';
    const PREVIOUS_VERSION = '%PREVIOUS_VERSION%';
    const NATIVE_VERSION = '%NATIVE_VERSION%';
    const API_VERSION = '%API_VERSION%';

    /**
     * @param string|array $type
     * @return bool
     */
    static function is($type)
    {
        if (is_array($type)) {
            foreach ($type as $t) {
                if (self::TYPE == strtoupper((string) $t)) {
                    return true;
                }
            }
            return false;
        }
        return self::TYPE == strtoupper((string) $type);
    }
}