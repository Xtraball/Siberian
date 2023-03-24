<?php

namespace Push2\Model;

use \Core_Model_Default as ModelDefault;

/**
 * Class Message
 * @package Push2\Model
 */
class Push extends ModelDefault {
    /**
     * @var bool
     */
    public static $individualEnabled = false;

    /**
     *
     */
    public static function enableIndividual ()
    {
        static::$individualEnabled = true;
    }

    /**
     * @return bool
     */
    public static function individualEnabled ()
    {
        return static::$individualEnabled;
    }
}

// important!
class_alias(Push::class, 'Push2_Model_Push');