<?php

namespace Siberian;

/**
 * Class \Siberian\Assets
 *
 * @id 1000
 *
 * @version 4.18.22
 *
 */
class Assets
{
    /**
     * Default excluded assets
     *
     * @var array
     */
    public static $exclude_files = [
        "\.htaccess",
        "\.gitignore",
        "\.DS_Store",
        "\.idea",
        "npm-debug.log",
        "chcp.json",
        "chcp.manifest",
        "css/app.css",
        "js/utils/url.js",
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
        // 4.15.0+ unused files
        "css/angular-carousel.min.css",
        "css/app.min.css",
        "css/ion-gallery.css",
        "css/ionic.app.min.css",
        "css/ionRadioFix.css",
        "css/ng-animation.css",
        "css/style.css",
        "js/app.js",
        "js/controllers/.*",
        "js/factory/.*",
        "js/libraries/.*",
        "js/services/.*",
        "js/MusicControls.js",
        "lib/ionic/css/.*",
        "lib/ionic/js/.*",
        "lib/ionic/scss/.*",
        "lib/ionic/version.json",
        "^templates/.*",
    ];

    public static $assets = [];

    /**
     * @var array
     */
    public static $assets_css = [];

    /**
     * @var array
     */
    public static $assets_js = [];

    /**
     * @var array
     */
    public static $assets_scss = [];

    /**
     * @var array
     */
    protected static $features_assets = [];


    /**
     * @var array
     */
    protected static $preBuildCallbacks = [];

    /**
     * @var array
     */
    protected static $postBuildCallbacks = [];

    /**
     * Available platforms
     *
     * @var array
     */
    public static $platforms = [
        'browser' => [
            '/var/apps/browser/',
            '/var/apps/overview/',
        ],
        'android' => [
            '/var/apps/ionic/android/',
        ],
        'ios' => [
            '/var/apps/ionic/ios/',
            '/var/apps/ionic/ios-noads/',
        ],
    ];

    /**
     * @var array
     */
    public static $www = [
        'browser' => '/',
        'android' => '/app/src/main/assets/www/',
        'ios' => '/www/',
    ];

    /**
     * Hook method to exclude assets, used for external modules
     *
     * @param array $assets
     */
    public static function excludeAssets($assets = [])
    {
        foreach ($assets as $asset) {
            self::$exclude_files[] = $asset;
        }
    }

    /**
     * Hook new platforms
     *
     * @param $type
     * @param $path
     */
    public static function addPlatform($type, $path)
    {
        // Skip previewers (this will lead to an error)
        if (strpos($path, '/previewer/') !== false) {
            return;
        }

        if (!isset(self::$platforms[$type])) {
            self::$platforms[$type] = [];
        }

        self::$platforms[$type][] = $path;
    }

    /**
     * @param $module
     * @param $from
     * @param array $exclude_types
     */
    public static function registerAssets($module, $from, $exclude_types = [])
    {
        if (!isset(self::$assets[$module])) {
            self::$assets[$module] = [
                "from" => $from,
                "exclude_types" => $exclude_types,
            ];
        }
    }

    /**
     * @param $files
     */
    public static function registerScss($files)
    {
        self::$assets_scss = array_merge(self::$assets_scss, $files);
    }

    /**
     * Trigger to copy all assets from the register
     */
    public static function copyAllAssets()
    {
        foreach (self::$assets as $module => $asset) {
            $from = $asset["from"];
            $exclude_types = $asset["exclude_types"];

            self::copyAssets($from, $exclude_types);
        }
    }

    /**
     * @param $from
     * @param null $exclude_types
     * @param string $to
     */
    public static function copyAssets($from, $exclude_types = null, $to = '')
    {
        if (!is_array($exclude_types)) {
            $exclude_types = [];
        }
        $base = path();
        foreach (self::$platforms as $type => $platforms) {
            if (!in_array($type, $exclude_types)) {
                $www = self::$www[$type];
                foreach ($platforms as $platform) {
                    $path_from = __ss(path($from));
                    $path_to = __ss(path(
                        $platform . $www . (strlen(trim($to)) > 0 ? "/" . $to : "")
                    ));

                    if ($base != $path_from) {
                        // Create directory tree if needed, useful since we now copy also single files
                        if (is_dir($path_from)) {
                            $dir_dest = $path_to;
                            $path_to .= "/";
                            $path_from .= "/*";
                        } else {
                            $dir_dest = dirname($path_to);
                        }

                        if (!is_dir($dir_dest)) {
                            mkdir(__ss($dir_dest), 0777, true);
                        }

                        // Ensure folders exists
                        if (file_exists(preg_replace("#/\*$#", "", $path_from))) {
                            exec("cp -r {$path_from} {$path_to}");
                            exec("chmod -R 777 {$path_to}");
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $dirpath
     * @param null $exclude_types
     */
    public static function destroyAssets($dirpath, $exclude_types = null)
    {
        if (!is_array($exclude_types)) {
            $exclude_types = [];
        }
        $base = path('');
        foreach (self::$platforms as $type => $platforms) {
            if (!in_array($type, $exclude_types)) {
                $www = self::$www[$type];
                foreach ($platforms as $platform) {
                    $path = path($platform . $www . $dirpath);

                    if (is_dir($path) && ($base != $path)) {
                        exec("rm -r {$path}");
                        // Ensure folders exists
                        if (file_exists($path)) {
                            exec("chmod -R 777 {$path}");
                        }
                    }
                }
            }
        }
    }

    /**
     * @throws \ErrorException
     * @throws \MatthiasMullie\Minify\Exceptions\IOException
     * @throws \Zend_Exception
     */
    public static function buildFeatures()
    {
        /**
         * @var $modules \Installer_Model_Installer_Module[]
         */
        $modules = (new \Installer_Model_Installer_Module())->findAll();


        $features = [];
        foreach ($modules as $module) {
            try {
                $module->fetch();
                $features = array_merge($features, $module->getFeatures(true));
            } catch (\Exception $e) {
                // Something went wrong!
            }
        }

        foreach ($features as $feature) {
            $output = [];
            $return = null;

            $name = $feature['name'];
            $category = $feature['category'];
            $code = $feature['code'];
            $model = $feature['model'];
            $desktop_uri = $feature['desktop_uri'];
            $open_callback_class = $feature['open_callback_class'] ?? null;
            $lazy_load = $feature['lazy_load'] ?? null;
            $my_account = (bool) ($feature['use_account'] ?? false);
            $only_once = (bool) ($feature['only_once'] ?? false);
            $mobile_uri = $feature['mobile_uri'] ?? ''; // Bypassing old _service modules with missing fake mobile_uri!
            $layouts = $feature['layouts'] ?? [];


            $icons = $feature['icons'];
            if (is_array($icons)) {
                $basePath = '/' . str_replace(path(), '', $feature['__DIR__']);
                $icons = array_map(
                    static function ($icon) use ($basePath) {
                        if (is_array($icon)) {
                            $icon['path'] = $basePath . '/' . $icon['path'];
                            return $icon;
                        }
                        return $basePath . '/' . $icon;
                    },
                    $icons
                );
            }

            $is_ajax = array_key_exists('is_ajax', $feature) ? ($feature['is_ajax'] !== false) : true;
            $social_sharing = array_key_exists('social_sharing', $feature) ? (bool) $feature['social_sharing'] : false;
            $nickname = array_key_exists('use_nickname', $feature) ? (bool) $feature['use_nickname'] : false;
            $ranking = array_key_exists('use_ranking', $feature) ? (bool) $feature['use_ranking'] : false;
            $civility = array_key_exists('use_civility', $feature) ? (bool) $feature['use_civility'] : false;
            $mobile = array_key_exists('use_mobile', $feature) ? (bool) $feature['use_mobile'] : false;
            $birthdate = array_key_exists('use_birthdate', $feature) ? (bool) $feature['use_birthdate'] : false;
            $critical_push = array_key_exists('use_critical_push', $feature) ? (bool) $feature['use_critical_push'] : false;

            $feature_dir = './features/' . $code;

            self::destroyAssets($feature_dir);
            if (is_dir($feature['__DIR__'] . '/assets')) {
                self::copyAssets($feature['__DIR__'] . '/assets', null, $feature_dir . '/assets');
            }

            if (is_dir($feature['__DIR__'] . '/templates')) {
                self::copyAssets($feature['__DIR__'] . '/templates', null, $feature_dir . '/templates');
            }

            // build index.js here
            $out_dir = path('var/tmp/out');
            if (!is_dir($out_dir)) {
                mkdir($out_dir, 0777, true);
            }

            $feature_js_path = $feature_dir . '/' . $code . '.js';
            $feature_js_bundle_path = $feature_dir . '/' . $code . '.bundle.min.js';

            $feature_js = self::compileFeature($feature, $feature_js_bundle_path);

            $built_file = $out_dir . '/' . $code . '.js';

            File::putContents($built_file, $feature_js);

            self::copyAssets($built_file, null, $feature_js_path);

            // Checks if the js & code key exists.
            if (!array_key_exists('js', self::$features_assets)) {
                self::$features_assets['js'] = [];
            }

            if (!array_key_exists($code, self::$features_assets['js'])) {
                self::$features_assets['js'][$code] = [];
            }

            // Fill the array with the value, ensure once!
            if (!in_array($feature_js_path, self::$features_assets['js'][$code], true)) {
                self::$features_assets['js'][$code][] = $feature_js_path;
            }

            $data = [
                'name' => $name,
                'code' => $code,
                'model' => $model,
                'desktop_uri' => $desktop_uri,
                'mobile_uri' => $mobile_uri,
                'use_my_account' => $my_account,
                'only_once' => $only_once,
                'is_ajax' => $is_ajax,
                'social_sharing_is_available' => $social_sharing,
                'lazy_load' => $lazy_load,
                'open_callback_class' => $open_callback_class,
                'use_nickname' => $nickname,
                'use_ranking' => $ranking,
                'use_civility' => $civility,
                'use_mobile' => $mobile,
                'use_birthdate' => $birthdate,
                'use_critical_push' => $critical_push,
            ];

            if (array_key_exists('position', $feature)) {
                $position = (int) $feature['position'] || null;
                if ($position) {
                    $data['position'] = $position;
                }
            } else {
                $data['position'] = null;
            }

            if (array_key_exists('custom_fields', $feature)) {
                $customFields = is_array($feature['custom_fields']) ? $feature['custom_fields'] : null;
                if ($customFields) {
                    $data['custom_fields'] = json_encode($customFields);
                }
            }

            // Install option & layouts only if it's a feature,
            // "_service" is a custom new category for "service modules"
            if ($category !== '_service') {
                $option = Feature::installFeature(
                    $category,
                    $data,
                    $icons
                );

                if (!empty($layouts)) {
                    Feature::installLayouts(
                        $option->getId(),
                        $code,
                        $layouts
                    );
                }
            }
        }
    }

    /**
     * @param $feature
     * @param null $bundle_path
     * @return string
     * @throws \MatthiasMullie\Minify\Exceptions\IOException
     */
    public static function compileFeature($feature, $bundle_path = null): string
    {
        // Validate ES5 module
        $nodePath = path('lib/Siberian/bin/node_64');
        if (__getConfig('ís_darwin')) {
            $nodePath .= '.osx';
        }
        $esCheckPath = path('lib/tools/node_modules/.bin/es-check');

        // Binaries must be executable (but es-check is not really a bin, just in case*)
        try {
            chmod($nodePath, 0777);
            chmod($esCheckPath, 0777);
        } catch (\Exception $e) {
            // Oups
        }

        exec('uname', $uname);
        if (strpos(strtolower(implode_polyfill('', $uname)), 'arwin') !== false) {
            $nodePath .= '.osx';
        }

        $code = $feature['code'];
        $minifier_js = new \MatthiasMullie\Minify\JS();
        $minifier_css = new \MatthiasMullie\Minify\CSS();

        $out_dir = path('var/tmp/out');
        if (!is_dir($out_dir) && !mkdir($out_dir, 0777, true)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $out_dir));
        }

        foreach ($feature['files'] as $file) {
            // Ignore files with ".." for security reasons!
            if (!preg_match("#\.\.#", $file)) {
                $inFile = $feature['__DIR__'] . '/' . $file;
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                if (is_readable($inFile)) {
                    if ($ext === 'scss') {
                        // SCSS Case
                        $css = self::compileScss($inFile);
                        $minifier_css->add($css);
                    }
                    if ($ext === 'js') {
                        $minifier_js->add($inFile);
                    }
                    if ($ext === 'css') {
                        $minifier_css->add($inFile);
                    }
                }
            }
        }

        // minify assets
        $bundle_css = $minifier_css->minify();
        $minifier_js->add("\nFeatures.insertCSS(" . json_encode($bundle_css) . ", \"" . $code . "\");");

        if ($bundle_path != null) {
            $tmp_file = "{$out_dir}/feature.{$code}.bundle.min.js";
            $minifier_js->minify($tmp_file);

            // Validate ES5 module
            try {
                $commandEs5 = implode_polyfill(' ', [
                    $nodePath,
                    $esCheckPath,
                    'es5',
                    $tmp_file
                ]);

                exec($commandEs5, $output, $return);

                // Debug statement for developers*
                if ($return === 1 && isDev()) {
                    dbg($output);
                }

                if ($return === 0) {
                    $featureJson = json_decode($feature["__JSON__"], JSON_OBJECT_AS_ARRAY);
                    $featureJson['es5_compliant'] = true;
                    $feature["__JSON__"] = json_encode($featureJson);
                }
            } catch (\Exception $e) {
                // Something went wrong while executing node_64, please check file has +x
            }

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
            __replace([
                "#App\.(info|constant|controller|config|factory|service|directive|run|provider|value|decorator|component|register|animation)#im" => 'angular.module("starter").$1',
            ], $tmp_file, true);

            self::copyAssets($tmp_file, null, $bundle_path);

            $output = " Features.register(" . $feature["__JSON__"] . ", ['{$bundle_path}']); ";

        } else {
            $output = $minifier_js->minify() . "\n;Features.register(" . $feature["__JSON__"] . "); ";
        }

        return $output;
    }

    /**
     * @param $path
     * @return string
     */
    public static function compileScss($path)
    {
        $compiler = Scss::getCompiler();
        $compiler->addImportPath(path("var/apps/browser/lib/ionic/scss"));
        $compiler->addImportPath(path("var/apps/browser/scss"));

        $content = [];
        $f = fopen(path("var/apps/browser/scss/ionic.siberian.variables-opacity.scss"), "r");
        if ($f) {
            while (($line = fgets($f)) !== false) {
                preg_match("/([\$a-zA-Z0-9_-]*)/", $line, $matches);
                if (!empty($matches[0]) AND !empty($variables[$matches[0]])) {
                    $line = "{$matches[0]}: {$variables[$matches[0]]} !default;";
                }
                $content[] = $line;
            }
        }
        $scss = implode_polyfill("\n", $content);


        // Import custom modules SCSS files!
        $customScss = file_get_contents($path);

        try {
            $css = $compiler->compile('
                @import "_variables.scss";
                @import "_mixins.scss";
                ' . $scss .
                $customScss
            );
        } catch (\Exception $e) {
            $css = "/** Error compiling custom module SCSS <" . $e->getMessage() . ">. */";
        }
        return $css;
    }

    /**
     * Compile all tepmlates in the $templateCache for angular
     *
     * @param $source
     */
    public static function buildTemplateCaches($source)
    {
        $phulp = new \Phulp\Phulp();
        $phulp->task('angular-template-cache', static function ($phulp) use ($source) {

            $phulp
                ->src([$source . '/templates/'], '/html$/')
                ->pipe(new \Phulp\AngularTemplateCache\AngularTemplateCache(
                    'templates-templates.js', [
                        'module' => 'templates',
                        'root' => 'templates/',
                    ]
                ))
                ->pipe($phulp->dest($source . '/dist/'));

            $phulp
                ->src([$source . '/modules/'], '/html$/')
                ->pipe(new \Phulp\AngularTemplateCache\AngularTemplateCache(
                    'templates-modules.js', [
                        'module' => 'templates',
                        'root' => 'modules/',
                    ]
                ))
                ->pipe($phulp->dest($source . '/dist/'));

            if (!file_exists($source . '/features/')) {
                mkdir($source . '/features/', 0775, true);
            }

            $phulp
                ->src([$source . '/features/'], '/html$/')
                ->pipe(new \Phulp\AngularTemplateCache\AngularTemplateCache(
                    'templates-features.js', [
                        'module' => 'templates',
                        'root' => 'features/',
                    ]
                ))
                ->pipe($phulp->dest($source . '/dist/'));
        });

        $phulp->run(["angular-template-cache"]);

        # Concat & Clean-up
        $content = file_get_contents($source . "/dist/templates-templates.js") . "\n"
            . file_get_contents($source . "/dist/templates-modules.js") . "\n"
            . file_get_contents($source . "/dist/templates-features.js");;

        File::putContents($source . "/dist/templates.js", $content);

        unlink($source . "/dist/templates-templates.js");
        unlink($source . "/dist/templates-modules.js");
        unlink($source . "/dist/templates-features.js");

    }

    /**
     * Re-build index.html with assets
     */
    public static function buildIndex()
    {
        self::buildFeatures();

        foreach (self::$platforms as $type => $platforms) {

            $www_folder = self::$www[$type];
            foreach ($platforms as $platform) {

                $path = path($platform);
                $index_path = $path . $www_folder . "index.html";
                $index_content = file_get_contents($index_path);


                $index_content = self::preBuildAction($index_content, $index_path, $type, $platform);

                // For browser/overview remove cdvfile
                if ($type === 'browser') {
                    $index_content = self::__cleanAppOnly($index_content);
                }

                if (in_array($type, ['browser', 'overview'])) {
                    // Replace available languages
                    $urlPath = $path . $www_folder . 'js/utils/url.js';
                    $urlContent = file_get_contents($urlPath);

                    $languages = array_map(static function ($_item) {
                        return "'{$_item}'";
                    }, array_keys(\Core_Model_Language::getLanguages()));

                    $urlContent = str_replace("['en']", "[" . implode_polyfill(', ', $languages) . "]", $urlContent);
                    file_put_contents($urlPath, $urlContent);
                }

                foreach (self::$preBuildCallbacks as $callback) {
                    $index_content = $callback($index_content, $index_path, $type, $platform);
                }

                # Build the templateCache
                self::buildTemplateCaches($path . $www_folder);

                foreach (self::$assets_js as $asset_js) {
                    $index_content = self::__appendAsset($index_content, $asset_js, "js");
                }

                foreach (self::$assets_css as $asset_css) {
                    $index_content = self::__appendAsset($index_content, $asset_css, "css");
                }

                // Collect which feature is already present in index.html
                preg_match_all("/<(?:script|link)[^>]+data-feature=\"([^\"]+)\"[^>]?>/", $index_content, $ins_features);
                if (is_array($ins_features)) {
                    $ins_features = array_unique($ins_features[1]);

                    // Remove all features from index.html
                    foreach ($ins_features as $f) {
                        $index_content = self::__removeAllFeatureAssets($index_content, $f);
                    }
                }

                // Add features to index.html
                foreach (['js', 'css'] as $_fType) {
                    if (array_key_exists($_fType, self::$features_assets)) {
                        foreach (self::$features_assets[$_fType] as $code => $assets) {
                            foreach ($assets as $asset) {
                                $index_content = self::__appendAsset($index_content, $asset, $_fType, $code);
                            }
                        }
                    }
                }

                foreach (self::$postBuildCallbacks as $callback) {
                    $index_content = $callback($index_content, $index_path, $type, $platform);
                }

                // Replace platform-browser for the overview, this is required after a restore app sources!
                if ($platform === '/var/apps/overview/') {
                    $index_content = str_replace('platform-browser', 'platform-overview', $index_content);
                }

                $index_content = self::postBuildAction($index_content, $index_path, $type, $platform);

                if (is_writable($index_path)) {
                    File::putContents($index_path, $index_content);
                }
            }
        }
    }

    /**
     * @param $code
     * @param $callback
     */
    public static function registerPreBuildCallback($code, $callback)
    {
        if (!is_string($code) || strlen(trim($code)) < 1) {
            throw new \InvalidArgumentException("code should be a non empty string. Input was: " . $code);
        }
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException("callback should be callable");
        }

        self::$preBuildCallbacks[$code] = $callback;
    }

    /**
     * @param $code
     * @param $callback
     */
    public static function unregisterPreBuildCallback($code, $callback)
    {
        unset(self::$preBuildCallbacks[$code]);
    }

    /**
     * @param $code
     * @param $callback
     */
    public static function registerPostBuildCallback($code, $callback)
    {
        if (!is_string($code) || strlen(trim($code)) < 1) {
            throw new \InvalidArgumentException("code should be a non empty string. Input was: " . $code);
        }
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException("callback should be callable");
        }

        self::$postBuildCallbacks[$code] = $callback;
    }

    /**
     * @param $code
     * @param $callback
     */
    public static function unregisterPostBuildCallback($code, $callback)
    {
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
    public static function preBuildAction($index_content, $index_path, $type, $platform)
    {
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
    public static function postBuildAction($index_content, $index_path, $type, $platform)
    {
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
    public static function __appendAsset($index_content, $asset_path, $type, $feature = null)
    {
        $asset_path = __ss($asset_path);
        $search = "</head>";
        $replace = self::___assetLine($asset_path, $type, $feature) . "</head>";

        if (strpos($index_content, $asset_path) === false) {
            $index_content = str_replace($search, $replace, $index_content);
        }

        return $index_content;
    }

    /**
     * @param $index_content
     * @param $asset_path
     * @param $type
     * @param null $feature
     * @return mixed
     */
    public static function __removeAsset($index_content, $asset_path, $type, $feature = null)
    {
        $asset_path = __ss($asset_path);
        $search = "</head>";
        $replace = self::___assetLine($asset_path, $type, $feature) . "</head>";

        if (strpos($index_content, $asset_path) === false) {
            $index_content = str_replace($search, $replace, $index_content);
        }

        return $index_content;
    }

    /**
     * @param $index_content
     * @return null|string|string[]
     */
    public static function __cleanAppOnly($index_content)
    {
        $index_content = preg_replace("/(<script.*cdvfile.*<\/script>)/mi", "", $index_content);

        return $index_content;
    }

    /**
     * @param $index_content
     * @param $feature
     * @param null $type
     * @return null|string|string[]
     */
    public static function __removeAllFeatureAssets($index_content, $feature, $type = null)
    {
        if ($type == null) {
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

        switch ($type) {
            case "js":
                $regex = "/\n?\t*<script[^<]+data-feature=\"{$feature}\"><\\/script>\n?\t*/";
                break;
            case "css":
                $regex = "/\n?\t*<link[^<]+data-feature=\"{$feature}\">\n?\t*/";
                break;
        }

        return preg_replace($regex, "", $index_content);
    }

    /**
     * @param $asset_path
     * @param $type
     * @param null $feature
     * @return string
     */
    public static function ___assetLine($asset_path, $type, $feature = null)
    {
        $asset_path = __ss($asset_path);
        $feature_data = is_string($feature) ? " data-feature=\"$feature\"" : "";
        switch ($type) {
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
    public static function addJavascripts($paths)
    {
        if (!is_array($paths)) {
            $paths = [$paths];
        }
        foreach ($paths as $path) {
            self::$assets_js[] = $path;
        }
    }

    /**
     * @param $paths
     */
    public static function addStylesheets($paths)
    {
        if (!is_array($paths)) {
            $paths = [$paths];
        }

        foreach ($paths as $path) {
            self::$assets_css[] = $path;
        }
    }
}
