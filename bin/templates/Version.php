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
                if (self::TYPE == strtoupper($t)) {
                    return true;
                }
            }
        }
        return self::TYPE == strtoupper($type);
    }
}