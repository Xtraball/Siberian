<?php

namespace Siberian;

/**
 * Class Flag
 *
 * A setting that is on or off, kept as '1' or '0' in the config table. A missing row, NULL, '' or any text that is not
 * a "true" word is off: the backoffice select always has an option to show ("No").
 */
class Flag
{
    /**
     * @param mixed $value
     * @return string '1' or '0'
     */
    public static function toSwitch($value): string
    {
        if (is_array($value)) {
            return '0';
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
    }
}
