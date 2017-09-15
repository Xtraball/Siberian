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
        "css/app.css",
        "js/utils/url.js",
        "js/utils/languages.js",
        "npm-debug.log",
        /// Unused build files to exclude
        "js/controllers/application.js",
        "js/controllers/booking.js",
        "js/controllers/catalog.js",
        "js/controllers/cms.js",
        "js/controllers/codescan.js",
        "js/controllers/contact.js",
        "js/controllers/customer.js",
        "js/controllers/discount.js",
        "js/controllers/event.js",
        "js/controllers/facebook.js",
        "js/controllers/folder.js",
        "js/controllers/form.js",
        "js/controllers/homepage.js",
        "js/controllers/image.js",
        "js/controllers/links.js",
        "js/controllers/locked.js",
        "js/controllers/loyalty-card.js",
        "js/controllers/maps.js",
        "js/controllers/media-player.js",
        "js/controllers/music.js",
        "js/controllers/newswall.js",
        "js/controllers/padlock.js",
        "js/controllers/places.js",
        "js/controllers/privacy-policy.js",
        "js/controllers/push.js",
        "js/controllers/radio.js",
        "js/controllers/rss.js",
        "js/controllers/set-meal.js",
        "js/controllers/social-gaming.js",
        "js/controllers/source-code.js",
        "js/controllers/tip.js",
        "js/controllers/topic.js",
        "js/controllers/twitter.js",
        "js/controllers/video.js",
        "js/controllers/weather.js",
        "js/controllers/wordpress.js",
        "js/directives/datetime.js",
        "js/directives/directives.bundle.js",
        "js/directives/sb-a-click.js",
        "js/directives/sb-album-boxes.js",
        "js/directives/sb-app-locked.js",
        "js/directives/sb-cms.js",
        "js/directives/sb-events.js",
        "js/directives/sb-google-autocomplete.js",
        "js/directives/sb-image.js",
        "js/directives/sb-input-number.js",
        "js/directives/sb-maps.js",
        "js/directives/sb-media-player-controls.js",
        "js/directives/sb-nav-view.js",
        "js/directives/sb-pad.js",
        "js/directives/sb-padlock.js",
        "js/directives/sb-page-background.js",
        "js/directives/sb-side-menu.js",
        "js/directives/sb-tabbar.js",
        "js/directives/sb-tooltip.js",
        "js/directives/sb-video.js",
        "js/factory/booking.js",
        "js/factory/catalog.js",
        "js/factory/cms.js",
        "js/factory/contact.js",
        "js/factory/customer.js",
        "js/factory/discount.js",
        "js/factory/event.js",
        "js/factory/facebook.js",
        "js/factory/folder.js",
        "js/factory/form.js",
        "js/factory/image.js",
        "js/factory/links.js",
        "js/factory/loyalty-card.js",
        "js/factory/maps.js",
        "js/factory/music.js",
        "js/factory/newswall.js",
        "js/factory/padlock.js",
        "js/factory/pages.js",
        "js/factory/places.js",
        "js/factory/push.js",
        "js/factory/radio.js",
        "js/factory/rss.js",
        "js/factory/search.js",
        "js/factory/set-meal.js",
        "js/factory/social-gaming.js",
        "js/factory/source-code.js",
        "js/factory/tc.js",
        "js/factory/tip.js",
        "js/factory/topic.js",
        "js/factory/twitter.js",
        "js/factory/video.js",
        "js/factory/weather.js",
        "js/factory/wordpress.js",
        "js/factory/youtube.js",
        "js/features/application.js",
        "js/features/booking.js",
        "js/features/catalog.js",
        "js/features/cms.js",
        "js/features/codescan.js",
        "js/features/contact.js",
        "js/features/discount.js",
        "js/features/event.js",
        "js/features/facebook.js",
        "js/features/features.bundle.js",
        "js/features/folder.js",
        "js/features/form.js",
        "js/features/homepage.js",
        "js/features/image.js",
        "js/features/links.js",
        "js/features/locked.js",
        "js/features/loyalty-card.js",
        "js/features/maps.js",
        "js/features/mcommerce.js",
        "js/features/media-player.js",
        "js/features/music.js",
        "js/features/newswall.js",
        "js/features/padlock.js",
        "js/features/places.js",
        "js/features/privacy-policy.js",
        "js/features/push.js",
        "js/features/radio.js",
        "js/features/rss.js",
        "js/features/set-meal.js",
        "js/features/social-gaming.js",
        "js/features/source-code.js",
        "js/features/tip.js",
        "js/features/topic.js",
        "js/features/twitter.js",
        "js/features/video.js",
        "js/features/weather.js",
        "js/features/wordpress.js",
        "js/filters/filters.js",
        "js/libraries/angular-queue.js",
        "js/packed/application.bundle.js",
        "js/packed/booking.bundle.js",
        "js/packed/catalog.bundle.js",
        "js/packed/cms.bundle.js",
        "js/packed/codescan.bundle.js",
        "js/packed/contact.bundle.js",
        "js/packed/discount.bundle.js",
        "js/packed/event.bundle.js",
        "js/packed/facebook.bundle.js",
        "js/packed/folder.bundle.js",
        "js/packed/form.bundle.js",
        "js/packed/homepage.bundle.js",
        "js/packed/image.bundle.js",
        "js/packed/links.bundle.js",
        "js/packed/loyalty_card.bundle.js",
        "js/packed/m_commerce.bundle.js",
        "js/packed/maps.bundle.js",
        "js/packed/media.bundle.js",
        "js/packed/newswall.bundle.js",
        "js/packed/padlock.bundle.js",
        "js/packed/places.bundle.js",
        "js/packed/privacy_policy.bundle.js",
        "js/packed/push.bundle.js",
        "js/packed/radio.bundle.js",
        "js/packed/rss.bundle.js",
        "js/packed/social_gaming.bundle.js",
        "js/packed/source_code.bundle.js",
        "js/packed/tip.bundle.js",
        "js/packed/topic.bundle.js",
        "js/packed/twitter.bundle.js",
        "js/packed/video.bundle.js",
        "js/packed/weather.bundle.js",
        "js/packed/wordpress.bundle.js",
        "js/packed/youtube.bundle.js",
        "js/providers/homepage-layout.js",
        "js/providers/providers.bundle.js",
        "js/providers/pwa-cache.js",
        "js/providers/pwa-request.js",
        "js/services/admob-service.js",
        "js/services/analytics.js",
        "js/services/application.js",
        "js/services/connection.js",
        "js/services/contextual-menu.js",
        "js/services/country.js",
        "js/services/dialog.js",
        "js/services/facebook-connect.js",
        "js/services/google-maps.js",
        "js/services/l10.js",
        "js/services/l17.js",
        "js/services/l8.js",
        "js/services/link-service.js",
        "js/services/loader.js",
        "js/services/location.js",
        "js/services/media-player.js",
        "js/services/modal.js",
        "js/services/music-tracks-loader.js",
        "js/services/picture.js",
        "js/services/progressbar-service.js",
        "js/services/push-service.js",
        "js/services/services.bundle.js",
        "js/services/session.js",
        "js/services/social-sharing.js",
        "js/services/tmhDynamicLocale.js",
        "js/services/translate.js",
        "js/services/url.js",
        "js/utils/features.js",
        "js/utils/form-post.js",
        "js/utils/languages.js",
        "js/utils/utils.bundle.js",
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
     * @var array
     */
    protected static $preBuildCallbacks = array();

    /**
     * @var array
     */
    protected static $postBuildCallbacks = array();

    /**
     * Available platforms
     *
     * @var array
     */
    public static $platforms = array(
        "browser" => array(
            "/var/apps/browser/",
            "/var/apps/overview/",
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
            $layouts = isset($feature["layouts"]) ? $feature["layouts"]: [];

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

            $feature_dir = "./features/".$code;

            self::destroyAssets($feature_dir);
            if(is_dir($feature["__DIR__"]."/assets")) {
                self::copyAssets($feature["__DIR__"]."/assets", null, $feature_dir."/assets");
            }

            if(is_dir($feature["__DIR__"]."/templates")) {
                self::copyAssets($feature["__DIR__"]."/templates", null, $feature_dir."/templates");
            }

            // build index.js here
            $out_dir = Core_Model_Directory::getBasePathTo("var/tmp/out");
            if(!is_dir($out_dir)) {
                mkdir($out_dir, 0777, true);
            }

            $feature_js_path = $feature_dir."/".$code.".js";
            $feature_js_bundle_path = $feature_dir."/".$code.".bundle.min.js";

            $feature_js = self::compileFeature($feature, $feature_js_bundle_path);

            $built_file = $out_dir."/".$code.".js";

            file_put_contents($built_file, $feature_js);

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

            $option = Siberian_Feature::installFeature(
                $category,
                $data,
                $icons
            );

            if(!empty($layouts)) {
                Siberian_Feature::installLayouts(
                    $option->getId(),
                    $code,
                    $layouts
                );
            }
        }
    }

    public static function compileFeature($feature, $bundle_path = null) {

        $code = $feature["code"];
        $feature_dir = "features/".$code;
        $minifier_js = new MatthiasMullie\Minify\JS();
        $minifier_css = new MatthiasMullie\Minify\CSS();

        $out_dir = Core_Model_Directory::getBasePathTo("var/tmp/out");
        if(!is_dir($out_dir)) {
            mkdir($out_dir, 0777, true);
        }

        foreach($feature["files"] as $file) {
            if(!preg_match("/(?:[\/\\]\.\.)?(?:\.\.[\/\\]?)/", $file)) { // ignore files with ".." for security reasons
                $in_file = $feature["__DIR__"]."/".$file;
                $out_file = $feature_dir."/".$file;
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                if(is_readable($in_file) && in_array($ext, array("js", "css"))) {
                    if($ext === "js") {
                        $minifier_js->add($in_file);
                    } elseif ($ext === "css") {
                        $minifier_css->add($in_file);
                    }
                }
            }
        }


        // minify assets
        $output = "";

        $bundle_css = $minifier_css->minify();
        $minifier_js->add("\nFeatures.insertCSS(".json_encode($bundle_css).", \"".$code."\");");

        if($bundle_path != null) {
            $tmp_file = "{$out_dir}/feature.{$code}.bundle.min.js";
            $minifier_js->minify($tmp_file);

            /** Replace
             * App.info,
             * App.constant,
             * App.controller,
             * App.config,
             * App.factory,
             * App.service,
             * App.directive,
             * App.run,
             * App.provider,
             * App.value,
             * App.decorator,
             * App.component,
             * App.register,
             * App.animation
             *
             * with angular.module("starter") for $ocLazyLoad */
            __replace(array(
                "#App\.(info|constant|controller|config|factory|service|directive|run|provider|value|decorator|component|register|animation)#im" => 'angular.module("starter").$1'
            ), $tmp_file, true);

            self::copyAssets($tmp_file, null, $bundle_path);

            $output = " Features.register(".$feature["__JSON__"].", ['{$bundle_path}']); ";

        } else {
            $output = $minifier_js->minify() . "\n;Features.register(".$feature["__JSON__"]."); ";
        }

        return $output;
    }

    /**
     * Compile all tepmlates in the $templateCache for angular
     *
     * @param $source
     */
    public static function buildTemplateCaches($source) {
        $phulp = new Phulp\Phulp();

        $phulp->task('angular-template-cache', function ($phulp) use ($source) {

            $phulp
                ->src([$source . '/templates/'], '/html$/')
                ->pipe(new Phulp\AngularTemplateCache\AngularTemplateCache(
                    'templates-templates.js', [
                        'module' => 'templates',
                        'root' => 'templates/'
                    ]
                ))
                ->pipe($phulp->dest($source . '/dist/'));

            $phulp
                ->src([$source . '/modules/'], '/html$/')
                ->pipe(new Phulp\AngularTemplateCache\AngularTemplateCache(
                    'templates-modules.js', [
                        'module' => 'templates',
                        'root' => 'modules/'
                    ]
                ))
                ->pipe($phulp->dest($source . '/dist/'));

            if(!file_exists($source . '/features/')) {
                mkdir($source . '/features/', 0775, true);
            }

            $phulp
                ->src([$source . '/features/'], '/html$/')
                ->pipe(new Phulp\AngularTemplateCache\AngularTemplateCache(
                    'templates-features.js', [
                        'module' => 'templates',
                        'root' => 'features/'
                    ]
                ))
                ->pipe($phulp->dest($source . '/dist/'));
        });

        $phulp->run('angular-template-cache');

        # Concat & Clean-up
        $content = file_get_contents($source . "/dist/templates-templates.js") . "\n"
                 . file_get_contents($source . "/dist/templates-modules.js") . "\n"
                 . file_get_contents($source . "/dist/templates-features.js");
        ;

        file_put_contents($source . "/dist/templates.js", $content);

        unlink($source . "/dist/templates-templates.js");
        unlink($source . "/dist/templates-modules.js");
        unlink($source . "/dist/templates-features.js");

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

                $index_content = self::preBuildAction($index_content, $index_path, $type, $platform);

                foreach(self::$preBuildCallbacks as $callback) {
                    $index_content = $callback($index_content, $index_path, $type, $platform);
                }

                # Build the templateCache, Siberian 5.0
                self::buildTemplateCaches($path.$www_folder);

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
    
                    // Remove all features from index.html
                    foreach($ins_features as $f) {
                        $index_content = self::__removeAllFeatureAssets($index_content, $f);
                    }
                }

                // Add features to index.html
                foreach(array("js", "css") as $type) {
                    foreach(self::$features_assets[$type] as $code => $assets) {
                        foreach($assets as $asset) {
                            $index_content = self::__appendAsset($index_content, $asset, $type, $code);
                        }
                    }
                }

                foreach(self::$postBuildCallbacks as $callback) {
                    $index_content = $callback($index_content, $index_path, $type, $platform);
                }

                $index_content = self::postBuildAction($index_content, $index_path, $type, $platform);

                if(is_writable($index_path)) {
                    file_put_contents($index_path, $index_content);
                }
            }
        }
    }

    public static function registerPreBuildCallback($code, $callback) {
        if(!is_string($code) || strlen(trim($code)) < 1) {
            throw new InvalidArgumentException("code should be a non empty string. Input was: ".$code);
        }
        if(!is_callable($callback)) {
            throw new InvalidArgumentException("callback should be callable");
        }

        self::$preBuildCallbacks[$code] = $callback;
    }

    public static function unregisterPreBuildCallback($code, $callback) {
        unset(self::$preBuildCallbacks[$code]);
    }

    public static function registerPostBuildCallback($code, $callback) {
        if(!is_string($code) || strlen(trim($code)) < 1) {
            throw new InvalidArgumentException("code should be a non empty string. Input was: ".$code);
        }
        if(!is_callable($callback)) {
            throw new InvalidArgumentException("callback should be callable");
        }

        self::$postBuildCallbacks[$code] = $callback;
    }

    public static function unregisterPostBuildCallback($code, $callback) {
        unset(self::$postBuildCallbacks[$code]);
    }

    /**
     *  When subclassing Siberian_Assets, you can override this method
     *  to add a preBuildAction on index.html build operations
     *
     * @param $index_content
     * @param $index_path
     * @param $type
     * @param $platform
     * @return string $index_content modified
     */
    public static function preBuildAction($index_content, $index_path, $type, $platform) {
        return $index_content;
    }

    /**
     *  When subclassing Siberian_Assets, you can override this method
     *  to add a postBuildAction on index.html build operations
     *
     * @param $index_content
     * @param $index_path
     * @param $type
     * @param $platform
     * @return string $index_content modified
     */
    public static function postBuildAction($index_content, $index_path, $type, $platform) {
        return $index_content;
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

    public static function __removeAllFeatureAssets($index_content, $feature, $type = null) {
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

    public static function ___assetLine($asset_path, $type, $feature = null) {
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
