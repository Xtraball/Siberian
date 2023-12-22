<?php

namespace Siberian;

/**
 * Class \Siberian\Version
 *
 * @ignore
 */
class Version
{
    const TYPE = 'SAE';
    const NAME = 'Single App Edition';
    const VERSION = '5.0.9';
    const PREVIOUS_VERSION = '5.0.8';
    const NATIVE_VERSION = '20';
    const API_VERSION = '4';

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
