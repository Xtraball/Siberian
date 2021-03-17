<?php

namespace Siberian;

/**
 * Class \Siberian\AdNetwork
 *
 * @version 4.20.7
 * @author Xtraball SAS <dev@xtraball.com>
 */

class AdNetwork
{
    /**
     * @var bool
     */
    public static $mediationEnabled = false;

    /**
     *
     */
    public static function enableMediation ()
    {
        static::$mediationEnabled = true;
    }
}
