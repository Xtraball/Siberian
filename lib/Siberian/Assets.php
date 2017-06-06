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
        "css/app.css",
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
     * @var array
     */
    protected static $features_assets = array();

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
    public static function copyAssets($from, $exclude_types = null, $to = "") {
        if(!is_array($exclude_types)) $exclude_types = array();
        $base = Core_Model_Directory::getBasePathTo("");
        foreach(self::$platforms as $type => $platforms) {
            if(!in_array($type, $exclude_types)) {
                $www = self::$www[$type];
                foreach($platforms as $platform) {
                    $path_from = __ss(Core_Model_Directory::getBasePathTo($from));
                    $path_to = __ss(Core_Model_Directory::getBasePathTo(
                        $platform.$www.(strlen(trim($to)) > 0 ? "/".$to : "")
                    ));

                    if($base != $path_from) {
                        // Create directory tree if needed, useful since we now copy also single files
                        if(is_dir($path_from)) {
                            $dir_dest =  $path_to;
                            $path_to .= "/";
                            $path_from .= "/*";
                        } else {
                            $dir_dest = dirname($path_to);
                        }

                        if(!is_dir($dir_dest)) {
                            mkdir(__ss($dir_dest), 0777, true);
                        }

                        exec("cp -r {$path_from} {$path_to}");
                        exec("chmod -R 775 {$path_to}");
                    }
                }
            }
        }
    }

    public static function destroyAssets($dirpath, $exclude_types = null) {
        if(!is_array($exclude_types)) $exclude_types = array();
        $base = Core_Model_Directory::getBasePathTo("");
        foreach(self::$platforms as $type => $platforms) {
            if(!in_array($type, $exclude_types)) {
                $www = self::$www[$type];
                foreach($platforms as $platform) {
                    $path = Core_Model_Directory::getBasePathTo($platform.$www.$dirpath);

                    if(is_dir($path)) {
                        if($base != $path) {
                            exec("rm -r {$path}");
                            exec("chmod -R 775 {$path}");
                        }
                    }
                }
            }
        }
    }

    public static function buildFeatures() {
        $module = new Installer_Model_Installer_Module();
        $modules = $module->findAll();

        $features = array();

        foreach($modules as $module) {
            $module->fetch();
            $features = array_merge($features, $module->getFeatures());
        }

        foreach($features as $feature) {
            $name = $feature["name"];
            $category = $feature["category"];
            $code = $feature["code"];
            $model = $feature["model"];
            $desktop_uri = $feature["desktop_uri"];
            $my_account = !!$feature["use_account"];
            $only_once = !!$feature["only_once"];
            $mobile_uri = $feature["mobile_uri"];

            $icons = $feature["icons"];
            if(is_array($icons)) {
                $basePath = "/".str_replace(Core_Model_Directory::getBasePathTo(""), "", $feature["__DIR__"]);
                $icons = array_map(
                    function($icon) use ($basePath) {
                        return $basePath."/".$icon;
                    },
                    $icons
                );
            }

            $is_ajax = $feature["is_ajax"] !== false;
            $social_sharing = !!$feature["social_sharing"];
            $nickname = !!$feature["use_nickname"];
            $ranking = !!$feature["use_ranking"];

            $feature_dir = "features/".$code;

            self::destroyAssets($feature_dir);
            if(is_dir($feature["__DIR__"]."/assets")) {
                self::copyAssets($feature["__DIR__"]."/assets", null, $feature_dir."/assets");
            }

            if(is_dir($feature["__DIR__"]."/templates")) {
                self::copyAssets($feature["__DIR__"]."/templates", null, $feature_dir."/templates");
            }

            // build index.js here
            $feature_js = self::compileFeature($feature, true, true);

            $out_dir = Core_Model_Directory::getBasePathTo("var/tmp/out");
            $built_file = $out_dir."/".$code.".js";
            if(!is_dir($out_dir)) mkdir($out_dir, 0777, true);

            file_put_contents($built_file, $feature_js);
            $feature_js_path = $feature_dir."/".$code.".js";
            self::copyAssets($built_file, null, $feature_js_path);
            if(!in_array($feature_js_path, self::$features_assets["js"][$code])) {
                self::$features_assets["js"][$code][] = $feature_js_path;
            }

            $data = array(
                "name" => $name,
                "code" => $code,
                "model" => $model,
                "desktop_uri" => $desktop_uri,
                "mobile_uri" => $mobile_uri,
                "use_my_account" => $my_account,
                "only_once" => $only_once,
                "is_ajax" => $is_ajax,
                "social_sharing_is_available" => $social_sharing,
                "use_nickname" => $nickname,
                "use_ranking" => $ranking,
            );

            $position = intval($feature["position"], 10) || null;
            if($position) $data["position"] = $position;

            $custom_fields = is_array($feature["custom_fields"]) ? $feature["custom_fields"] : null;
            if($custom_fields) $data["custom_fields"] = json_encode($custom_fields);

            Siberian_Feature::installFeature(
                $category,
                $data,
                $icons
            );
        }
    }

    public static function compileFeature($feature, $copy_assets = false, $insert_assets = false) {
        $feature_js = "";
        $code = $feature["code"];
        $feature_dir = "features/".$code;

        $feature_register = " Features.register(".$feature["__JSON__"]."); ";

        foreach($feature["files"] as $file) {
            if(!preg_match("/(?:[\/\\]\.\.)?(?:\.\.[\/\\]?)/", $file)) { // ignore files with ".." for security reasons
                $in_file = $feature["__DIR__"]."/".$file;
                $out_file = $feature_dir."/".$file;
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                if(is_readable($in_file) && in_array($ext, array("js", "css"))) {
                    if($feature["compile"]) {
                        if($ext === "js") {
                            $feature_js .= file_get_contents($in_file)." ";
                        } elseif ($ext === "css") {
                            $feature_js .= "Features.insertCSS(".json_encode(file_get_contents($in_file)).", \"".$code."\");";
                        }
                    } else {
                        if($copy_assets) {
                            self::copyAssets($in_file, null, $out_file);
                        }
                        if($insert_assets) {
                            if($ext === "css" && !in_array($out_file, self::$features_assets["css"][$code])) {
                                self::$features_assets["css"][$code][] = $out_file;
                            } elseif ($ext === "js" && !in_array($out_file, self::$features_assets["js"][$code])) {
                                self::$features_assets["js"][$code][] = $out_file;
                            }
                        }
                    }
                }
            }
        }

        $feature_js = $feature_js.$feature_register;

        return $feature_js;
    }

    /**
     * Re-build index.html with assets
     */
    public static function buildIndex() {
        self::buildFeatures();

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

                // Collect which feature is already present in index.html
                preg_match_all("/<(?:script|link)[^>]+data-feature=\"([^\"]+)\"[^>]?>/", $index_content, $ins_features);
                if(is_array($ins_features)) {
                    $ins_features = array_unique($ins_features[1]);
                }

                // Add features to index.html
                $reg_features = array();
                foreach(array("js", "css") as $type) {
                    foreach(self::$features_assets[$type] as $code => $assets) {
                        if(!in_array($code, $reg_features)) {
                            $reg_features[] = $code; // keep track of which features has been added
                        }
                        foreach($assets as $asset) {
                            $index_content = self::__appendAsset($index_content, $asset, $type, $code);
                        }
                    }
                }

                // Remove not registered features from index.html
                $features_to_del = array_diff($ins_features, $reg_features);
                foreach($features_to_del as $f) {
                    $index_content = self::__removeAllFeatureAssets($index_content, $f);
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
    public static function __appendAsset($index_content, $asset_path, $type, $feature = null) {
        $asset_path = __ss($asset_path);
        $search = "</head>";
        $replace = self::___assetLine($asset_path, $type, $feature)."</head>";

        if(strpos($index_content, $asset_path) === false) {
            $index_content = str_replace($search, $replace, $index_content);
        }

        return $index_content;
    }

    public static function __removeAsset($index_content, $asset_path, $type, $feature = null) {
        $asset_path = __ss($asset_path);
        $search = "</head>";
        $replace = self::___assetLine($asset_path, $type, $feature)."</head>";

        if(strpos($index_content, $asset_path) === false) {
            $index_content = str_replace($search, $replace, $index_content);
        }

        return $index_content;
    }

    private static function __removeAllFeatureAssets($index_content, $feature, $type = null) {
        if($type == null) {
            return self::__removeAllFeatureAssets(
                self::__removeAllFeatureAssets(
                    $index_content,
                    $feature,
                    "js"
                ),
                $feature,
                "css"
            );
        }

        switch($type) {
        case "js":
            $regex = "/\n?\t*<script[^<]+data-feature=\"${feature}\"><\\/script>\n?\t*/";
            break;
        case "css":
            $regex = "/\n?\t*<link[^<]+data-feature=\"${feature}\">\n?\t*/";
            break;
        }

        return preg_replace($regex, "", $index_content);
    }

    private static function ___assetLine($asset_path, $type, $feature = null) {
        $asset_path = __ss($asset_path);
        $feature_data = is_string($feature) ? " data-feature=\"$feature\"" : "";
        switch($type) {
        case 'js':
            $replace = "\n\t\t<script src=\"{$asset_path}\"{$feature_data}></script>\n\t";
            break;
        case 'css':
            $replace = "\n\t\t<link href=\"{$asset_path}\" rel=\"stylesheet\"{$feature_data}>\n\t";
            break;
        }

        return $replace;
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
