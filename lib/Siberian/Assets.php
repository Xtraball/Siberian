<?php

/**
 * Class Siberian_Assets
 *
 * @id 1000
 *
 * @version 4.4.2
 *
 */

class Siberian_Assets
{
    /**
     * Default excluded assets
     *
     * @var array
     */
    public static $exclude_files = array(
        "\.gitignore",
        "\.DS_Store",
        "\.idea",
        "npm-debug.log",
        "chcp.json",
        "chcp.manifest",
        "js/utils/url.js",
        "js/utils/languages.js",
    );

    public static $assets = array();

    /**
     * @var array
     */
    public static $assets_css = array();

    /**
     * @var array
     */
    public static $assets_js = array();

    /**
     * Available platforms
     *
     * @var array
     */
    public static $platforms = array(
        "browser" => array(
            "/var/apps/browser/",
        ),
        "android" => array(
            "/var/apps/ionic/android/",
        ),
        "ios" => array(
            "/var/apps/ionic/ios/",
            "/var/apps/ionic/ios-noads/",
        ),
    );

    public static $www = array(
        "browser" => "/",
        "android" => "/assets/www/",
        "ios" => "/www/",
    );

    public static $config_xml = array(
        "android" => "/res/xml/config.xml",
        "ios" => "/AppsMobileCompany/config.xml",
    );

    /**
     * Hook method to exclude assets, used for external modules
     *
     * @param array $assets
     */
    public static function excludeAssets($assets = array()) {
        foreach($assets as $asset) {
            self::$exclude_files[] = $asset;
        }
    }

    /**
     * Hook new platforms
     *
     * @param $type
     * @param $path
     */
    public static function addPlatform($type, $path) {
        if(!isset(self::$platforms[$type])) {
            self::$platforms[$type] = array();
        }

        self::$platforms[$type][] = $path;
    }

    /**
     * @param $module
     * @param $from
     * @param array $exclude_types
     */
    public static function registerAssets($module, $from, $exclude_types = array()) {
        if(!isset(self::$assets[$module])) {
            self::$assets[$module] = array(
                "from" => $from,
                "exclude_types" => $exclude_types,
            );
        }
    }

    /**
     * Trigger to copy all assets from the register
     */
    public static function copyAllAssets() {
        foreach(self::$assets as $module => $asset) {
            $from = $asset["from"];
            $exclude_types = $asset["exclude_types"];

            self::copyAssets($from, $exclude_types);
        }
    }

    /**
     * @param $from
     */
    public static function copyAssets($from, $exclude_types = array()) {
        $base = Core_Model_Directory::getBasePathTo("");
        foreach(self::$platforms as $type => $platforms) {
            if(!in_array($type, $exclude_types)) {
                $www = self::$www[$type];
                foreach($platforms as $platform) {
                    $path_from = Core_Model_Directory::getBasePathTo($from);
                    $path_to = Core_Model_Directory::getBasePathTo($platform.$www);

                    if($base != $path_from) {
                        exec("cp -r {$path_from}/* {$path_to}/");
                        exec("chmod -R 775 {$path_to}/");
                    }
                }
            }
        }
    }

    /**
     * Re-build index.html with assets
     */
    public static function buildIndex() {
        foreach(self::$platforms as $type => $platforms) {

            $www_folder = self::$www[$type];
            foreach($platforms as $platform) {
                $path = Core_Model_Directory::getBasePathTo($platform);
                $index_path = $path.$www_folder."index.html";
                $index_content = file_get_contents($index_path);

                foreach(self::$assets_js as $asset_js) {
                    $index_content = self::__appendAsset($index_content, $asset_js, "js");
                }

                foreach(self::$assets_css as $asset_css) {
                    $index_content = self::__appendAsset($index_content, $asset_css, "css");
                }

                if(is_writable($index_path)) {
                    file_put_contents($index_path, $index_content);
                }
            }
        }
    }

    /**
     * Append assets to every registered index.html
     *
     * @param $index_content
     * @param $asset_path
     * @param $type
     * @return mixed
     */
    public static function __appendAsset($index_content, $asset_path, $type) {
        $asset_path = __ss($asset_path);
        $search = "</head>";
        switch($type) {
            case 'js':
                    $replace = "\n\t\t<script src=\"{$asset_path}\"></script>\n\t</head>";
                break;
            case 'css':
                    $replace = "\n\t\t<link href=\"{$asset_path}\" rel=\"stylesheet\">\n\t</head>";
                break;
        }

        if(strpos($index_content, $asset_path) === false) {
            $index_content = str_replace($search, $replace, $index_content);
        }

        return $index_content;
    }

    /**
     * @param $paths
     */
    public static function addJavascripts($paths) {
        if(!is_array($paths)) {
            $paths = array($paths);
        }
        foreach($paths as $path) {
            self::$assets_js[] = $path;
        }
    }

    /**
     * @param $paths
     */
    public static function addStylesheets($paths) {
        if(!is_array($paths)) {
            $paths = array($paths);
        }

        foreach($paths as $path) {
            self::$assets_css[] = $path;
        }
    }
}
