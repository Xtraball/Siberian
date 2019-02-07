<?php
/**
 * @ignore
 */


namespace Siberian;

/**
 * Class \Siberian\Version
 */
class Version
{
    const TYPE = 'SAE';
    const NAME = 'Single App Edition';
    const VERSION = '4.15.14';
    const NATIVE_VERSION = '9';
    const API_VERSION = '1';

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