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
     * @var array
     */
    public static $editor_menus = array();

    /**
     * @var array
     */
    public static $modules_roots = array();

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
     * @param $module
     * @param $code
     * @param $title
     * @param $link
     * @param $icon
     */
    public static function addMenu($module, $code, $title, $link, $icon = '') {
        if(!isset(self::$menus[$module])) {
            self::$menus[$module] = array();
        }

        if(!isset(self::$menus[$module][$code])) {
            self::$menus[$module][$code] = array(
                "title" => __($title),
                "link" => $link,
                "icon" => $icon,
            );
        }
    }

    /**
     * @return array|bool
     */
    public static function getMenus() {
        if(!empty(self::$menus)) {
            return self::$menus;
        }
        return false;
    }

    /**
     * @param $module
     * @param $code
     * @param $title
     * @param $link
     * @param $icon
     */
    public static function addEditorMenu($module, $code, $title, $link, $icon = '') {
        if(!isset(self::$editor_menus[$module])) {
            self::$editor_menus[$module] = array();
        }

        if(!isset(self::$editor_menus[$module][$code])) {
            self::$editor_menus[$module][$code] = array(
                "title" => __($title),
                "link" => $link,
                "icon" => $icon,
            );
        }
    }

    /**
     * @return array|bool
     */
    public static function getEditorMenus() {
        if(!empty(self::$editor_menus)) {
            return self::$editor_menus;
        }
        return false;
    }

    /**
     * @param $module
     */
    public static function addModuleRoot($module, $root) {
        $moduleKey = trim(strtolower($module));
        if(!isset(self::$modules_roots[$moduleKey])) {
            self::$modules_roots[$moduleKey] = $root;
        }
    }

    /**
     * @return array|bool
     */
    public static function getModuleRoot($module) {
        $moduleKey = trim(strtolower($module));
        if(!empty(self::$modules_roots[$moduleKey])) {
            return self::$modules_roots[$moduleKey];
        }
        return false;
    }
}