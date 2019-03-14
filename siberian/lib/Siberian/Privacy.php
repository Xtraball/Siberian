<?php

namespace Siberian;

/**
 * Class Privacy
 * @package Siberian
 */
class Privacy
{
    /**
     * @var array
     */
    public static $registeredModules = [];

    /**
     * @param $module
     * @param $title
     * @param null $templatePath
     */
    public static function registerModule($module, $title, $templatePath = null)
    {
        if (!array_key_exists($module, self::$registeredModules)) {
            self::$registeredModules[$module] = [
                "code" => $module,
                "label" => $title,
                "templatePath" => $templatePath,
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
