<?php

/**
 * Class Siberian_Module
 */
class Siberian_Module
{

    /**
     * @var array
     */
    public static $actions = [];

    /**
     * @var array
     */
    public static $menus = [];

    /**
     * @var array
     */
    public static $editor_menus = [];

    /**
     * @var array
     */
    public static $modules_roots = [];

    /**
     * @var array
     */
    public static $coreModules = [
        'Acl',
        'Admin',
        'Analytics',
        'Api',
        'Application',
        'Backoffice',
        'Booking',
        'Cache',
        'Catalog',
        'Cms',
        'Codescan',
        'Comment',
        'Contact',
        'Core',
        'Cron',
        'Customer',
        'Event',
        'Fanwall',
        'Firewall',
        'Folder',
        'Folder2',
        'Form',
        'Form2',
        'Front',
        'Importer',
        'InAppPurchase',
        'Installer',
        'Job',
        'Layouts',
        'LoyaltyCard',
        'Mail',
        'Maps',
        'Mcommerce',
        'Media',
        'Message',
        'Padlock',
        'Payment',
        'PaymentCash',
        'PaymentMethod',
        'PaymentStripe',
        'Places',
        'Preview',
        'Privacypolicy',
        'Promotion',
        'Push',
        'Radio',
        'Rss',
        'Session',
        'Social',
        'Socialgaming',
        'Sourcecode',
        'System',
        'Template',
        'TemplateBlank',
        'TemplateBleuc',
        'TemplateColors',
        'TemplateRouse',
        'Tip',
        'Topic',
        'Tour',
        'Translation',
        'Twitter',
        'Weather',
        'Weblink',
        'Wordpress',
        'Wordpress2',
        'Sales',
        'Subscription',
        'Tax',
        'Whitelabel'
    ];

    /**
     * @param $feature
     * @param $classname
     */
    public static function addActions($module, $actions = [])
    {
        if (!isset(self::$actions[$module])) {
            self::$actions[$module] = $actions;
        }
    }

    /**
     * @param $module
     * @return bool|mixed
     */
    public static function getActions($module)
    {
        if (isset(self::$actions[$module])) {
            return self::$actions[$module];
        }
        return false;
    }

    /**
     * @param $module
     * @param $code
     * @param $title
     * @param $link
     * @param string $icon
     * @param null $context
     * @throws Zend_Exception
     */
    public static function addMenu($module, $code, $title, $link, $icon = '', $context = null)
    {
        if (!isset(self::$menus[$module])) {
            self::$menus[$module] = [];
        }

        if (!isset(self::$menus[$module][$code])) {
            self::$menus[$module][$code] = [
                "title" => $title,
                "link" => $link,
                "context" => $context,
                "icon" => $icon,
            ];
        }
    }

    /**
     * @return array|bool
     */
    public static function getMenus()
    {
        if (!empty(self::$menus)) {
            return self::$menus;
        }
        return false;
    }

    /**
     * @param $module
     * @param $code
     * @param $title
     * @param $link
     * @param string $icon
     * @param null $aclCode
     * @param null $context
     * @throws Zend_Exception
     */
    public static function addEditorMenu($module, $code, $title, $link, $icon = '', $aclCode = null, $context = null)
    {
        if (!isset(self::$editor_menus[$module])) {
            self::$editor_menus[$module] = [];
        }

        if (!isset(self::$editor_menus[$module][$code])) {
            self::$editor_menus[$module][$code] = [
                "title" => $title,
                "link" => $link,
                "context" => $context,
                "icon" => $icon,
                "code" => $code,
                "acl_code" => $aclCode,
            ];
        }
    }

    /**
     * @return array|bool
     */
    public static function getEditorMenus()
    {
        if (!empty(self::$editor_menus)) {
            return self::$editor_menus;
        }
        return false;
    }

    /**
     * @param $module
     * @param $root
     */
    public static function addModuleRoot($module, $root)
    {
        $moduleKey = strtolower(trim($module));
        if (!isset(self::$modules_roots[$moduleKey])) {
            self::$modules_roots[$moduleKey] = $root;
        }
    }

    //**

    public static function getModuleRoot($module)
    {
        $moduleKey = strtolower(trim($module));
        if (!empty(self::$modules_roots[$moduleKey])) {
            return self::$modules_roots[$moduleKey];
        }
        return false;
    }
}
