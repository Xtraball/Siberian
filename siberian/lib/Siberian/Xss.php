<?php

// https://www.owasp.org/index.php/XSS_(Cross_Site_Scripting)_Prevention_Cheat_Sheet

namespace Siberian;

/**
 * Class Xss
 * @package Siberian
 */
Class Xss
{
    const LEVEL_LOW = 1;
    const LEVEL_MEDIUM = 5;
    const LEVEL_HIGH = 10;

    /**
     * @param $string
     * @param int $level
     * @param $config
     * @return mixed
     * @throws \Zend_Exception
     */
    public static function sanitize($string, $level = self::LEVEL_HIGH, $config = null)
    {
        switch ($level) {
            case self::LEVEL_LOW:


                break;
            case self::LEVEL_MEDIUM:


                break;
            case self::LEVEL_HIGH:
                    $string = \purify($string, $config);
                break;
        }

        return $string;
    }
}