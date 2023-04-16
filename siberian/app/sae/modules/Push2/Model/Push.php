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
    static $individualEnabled = false;

    /**
     * @return bool
     */
    public static function individualEnabled() {
        return self::$individualEnabled;
    }

    /**
     * @return void
     */
    public static function enableIndividual() {
        self::$individualEnabled = true;
    }
}

// important!
class_alias(Push::class, 'Push2_Model_Push');