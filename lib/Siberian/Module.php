<?php
/**
 *
 */
class Siberian_Module {

    public static $actions = array();

    /**
     * @param $feature
     * @param $classname
     */
    public static function addActions($module, $actions = array()) {
        if(!isset(self::$actions[$module])) {
            self::$actions[$module] = $actions;
        }
    }

    /**
     * @param $module
     * @return bool|mixed
     */
    public static function getActions($module) {
        if(isset(self::$actions[$module])) {
            return self::$actions[$module];
        }
        return false;
    }
}