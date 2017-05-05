<?php
/**
 *
 */
class Siberian_Module {

    /**
     * @var array
     */
    public static $actions = array();

    /**
     * @var array
     */
    public static $menus = array();

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

    /**
     * @param $feature
     * @param $classname
     */
    public static function addMenu($module, $code, $title, $link) {
        if(!isset(self::$menus[$module])) {
            self::$menus[$module] = array();
        }

        if(!isset(self::$menus[$module][$code])) {
            self::$menus[$module][$code] = array(
                "title"     => __($title),
                "link"      => $link,
            );
        }
    }

    /**
     * @param $module
     * @return bool|mixed
     */
    public static function getMenus() {
        if(!empty(self::$menus)) {
            return self::$menus;
        }
        return false;
    }
}