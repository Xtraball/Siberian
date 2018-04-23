<?php

/**
 * Class Siberian_Privacy
 *
 * @version 4.13.14
 *
 */

class Siberian_Privacy
{
    /**
     * @var array
     */
    public static $registeredModules = [];

    /**
     * @param $module
     */
    public static function registerModule($module, $title, $templatePath = null)
    {
        if (!array_key_exists($module, self::$registeredModules)) {
            self::$registeredModules[$module] = [
                'code' => $module,
                'label' => $title,
                'templatePath' => $templatePath,
            ];
        }
    }

    /**
     * @return array
     */
    public static function getRegisteredModules()
    {
        return self::$registeredModules;
    }

}
