<?php

namespace Siberian;

require path("/lib/vendor/matthiasmullie/minify/src/Minify.php");

use MatthiasMullie\Minify\Minify as AbstractMinify;
use MatthiasMullie\Minify\CSS as CSS;
use MatthiasMullie\Minify\JS as JS;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

/**
 * Class \Siberian\Minify
 */
class Minify extends AbstractMinify
{

    /**
     * @var array
     */
    public static $EXCLUDE_CSS = [];

    /**
     * @var array
     */
    public static $EXCLUDE_JS = [
        'dist/app.libs-min.js',
        'js/utils/languages.js',
        'js/utils/url.js',
        'cordova.js',
    ];

    /**
     * @var array
     */
    public static $PLATFORMS = [
        'browser' => [
            'css' => true,
            'js' => true,
            'base' => 'var/apps/browser',
            'index' => 'var/apps/browser/index.html',
            'output_css' => 'var/apps/browser/dist/app.bundle-min.css',
            'output_js' => 'var/apps/browser/dist/app.bundle-min.js',
        ],
        'overview' => [
            'css' => true,
            'js' => true,
            'base' => 'var/apps/overview',
            'index' => 'var/apps/overview/index.html',
            'output_css' => 'var/apps/overview/dist/app.bundle-min.css',
            'output_js' => 'var/apps/overview/dist/app.bundle-min.js',
        ],
        'android' => [
            'css' => true,
            'js' => false,
            'base' => 'var/apps/ionic/android/app/src/main/assets/www',
            'index' => 'var/apps/ionic/android/app/src/main/assets/www/index.html',
            'output_css' => 'var/apps/ionic/android/app/src/main/assets/www/dist/app.bundle-min.css',
            'output_js' => 'var/apps/ionic/android/app/src/main/assets/www/dist/app.bundle-min.js',
        ],
        'ios' => [
            'css' => true,
            'js' => false,
            'base' => 'var/apps/ionic/ios/www',
            'index' => 'var/apps/ionic/ios/www/index.html',
            'output_css' => 'var/apps/ionic/ios/www/dist/app.bundle-min.css',
            'output_js' => 'var/apps/ionic/ios/www/dist/app.bundle-min.js',
        ],
    ];

    /**
     * @var array
     */
    public static $ASSETS = [];

    /**
     * @var string
     */
    public static $ASSETS_CACHE = 'var/cache/assets.cache';

    /**
     * @var string
     */
    public static $basepath;

    /**
     * @var null|Siberian_Minify
     */
    public static $instance = null;

    /**
     * Siberian_Minify constructor.
     */
    public function __construct()
    {
        if (is_null(self::$instance)) {
            self::$basepath = path("");

            foreach (self::$PLATFORMS as $platform => $path) {
                $basepath = self::$basepath;
                self::$PLATFORMS[$platform]['index'] = "{$basepath}{$path['index']}";
            }

            /** app.ini config is disabled */

            self::$instance = $this;
        }

        return self::$instance;
    }

    /**
     * @param $name
     * @param array $options
     */
    public static function addPlatform($name, $options = [])
    {
        if (!isset(self::$PLATFORMS[$name])) {
            self::$PLATFORMS[$name] = $options;
        }
    }

    /**
     * @param null $path
     * @return string|void
     */
    public function execute($path = null)
    {
        parent::execute($path);
    }

    /**
     *
     */
    public function build()
    {
        foreach (self::$PLATFORMS as $platform => $path) {
            $doCss = self::$PLATFORMS[$platform]['css'];
            $doJs = self::$PLATFORMS[$platform]['js'];
            $indexPath = self::$PLATFORMS[$platform]['index'];
            $outputCss = self::$PLATFORMS[$platform]['output_css'];
            $outputJs = self::$PLATFORMS[$platform]['output_js'];

            /** Build only if files are not already cached */
            if ($doCss &&
                !is_file($outputCss)) {
                $this->minifyCss($platform, $indexPath, $outputCss);
            }

            if ($doJs &&
                !is_file($outputJs)) {
                $this->minifyJs($platform, $indexPath, $outputJs);
            }

            $indexDest = str_replace('index', 'index-prod', $indexPath);
            if (!is_file($indexDest)) {
                $this->replaceIndex($platform, $indexPath, $doCss, $doJs);
            }

            // LAter we will build service-workers.
            //$this->buildServiceWorker();
        }


    }

    /**
     * @ignore
     */
    public function buildServiceWorker()
    {
        return;

        /**
        $base = self::$PLATFORMS["browser"]["base"];
        //$manifest_file = $base . "/" . Siberian_Autoupdater::$pwa_manifest;
        //$current_release = System_Model_Config::getValueFor("current_release");

        $app_shell_files = Json::decode(file_get_contents(path($manifest_file)));

        # Modules
        if (file_exists($base . "/modules")) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base . "/modules", 4096), RecursiveIteratorIterator::SELF_FIRST);
            foreach ($files as $file) {
                if ($file->isDir() || $file->isLink() || (strpos($file->getFilename(), ".") === 0)) {
                    continue;
                }

                $app_shell_files[] = str_replace($base . "/", "", $file->getPathname());

            }
        } else {
            mkdir($base . "/modules", 0775, true);
        }

        # Features
        if (file_exists($base . "/features")) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base . "/features", 4096), RecursiveIteratorIterator::SELF_FIRST);
            foreach ($files as $file) {
                if ($file->isDir() || $file->isLink() || (strpos($file->getFilename(), ".") === 0)) {
                    continue;
                }

                $app_shell_files[] = str_replace($base . "/", "", $file->getPathname());

            }
        } else {
            mkdir($base . "/features", 0775, true);
        }

        # Plugins
        if (file_exists($base . "/plugins")) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base . "/plugins", 4096), RecursiveIteratorIterator::SELF_FIRST);
            foreach ($files as $file) {
                if ($file->isDir() || $file->isLink() || (strpos($file->getFilename(), ".") === 0)) {
                    continue;
                }

                $app_shell_files[] = str_replace($base . "/", "", $file->getPathname());

            }
        } else {
            mkdir($base . "/plugins", 0775, true);
        }

        $app_shell_files[] = "dist/app.bundle-min.js?version=" . $current_release;

        # Write to file
        //$template = path($base . "/" . "pwa-worker-template.js");
        //$destination = path($base . "/" . "pwa-worker.js");
        //$service_worker = file_get_contents($template);

        $toreplace = Json::encode($app_shell_files);


        /**$service_worker = str_replace("var filesToCache = [];",
         * "var filesToCache = " . $toreplace . ";", $service_worker);
         * $service_worker = str_replace("var cacheName = \"pwa-worker-\" + Date.now();",
         * "var cacheName = \"pwa-worker-" . System_Model_Config::getValueFor("current_release") . "\";", $service_worker);
         *
         * file_put_contents($destination, $service_worker);*/
    }

    /**
     * @param $platform
     * @param $index_path
     * @param $output_css
     */
    public function minifyCss($platform, $index_path, $output_css)
    {
        $regex = '/<link href="([a-z0-9\.\/\-_]+\.css)" rel="stylesheet">/mi';

        $this->_minify("css", $regex, $index_path, $output_css, $platform);
    }

    /**
     * @param $platform
     * @param $indexPath
     * @param $outputJs
     * @throws \MatthiasMullie\Minify\Exceptions\IOException
     */
    public function minifyJs($platform, $indexPath, $outputJs)
    {
        $regex = '/<script[^>]+src="([a-z0-9\.\/\-_]+\.js)"/mi';

        $this->_minify('js', $regex, $indexPath, $outputJs, $platform);
    }

    /**
     * @param $type
     * @param $regex
     * @param $content
     * @param $output
     * @param $platform
     */
    private function _minify($type, $regex, $content, $output, $platform)
    {
        if (!is_readable($content)) {
            return;
        }
        $index_content = file_get_contents($content);

        $basepath = dirname($content);

        switch ($type) {
            case 'css':
                $minifier = new CSS();
                $minifier->setMaxImportSize(5000);
                $exclude = self::$EXCLUDE_CSS;
                break;
            case 'js':
                $minifier = new JS();
                $exclude = self::$EXCLUDE_JS;
                break;
            default:
                // Stop here, unsupported file type.
                return;
        }

        /** Do not exclude js for browser/pwa */
        if ((($platform === 'browser') || ($platform === 'overview')) && ($type === 'js')) {
            $exclude = [];
        }

        $matches = [];
        /** Match all css */
        if (preg_match_all($regex, $index_content, $matches)) {
            foreach ($matches[1] as $match) {
                if (!in_array($match, $exclude) &&
                    file_exists("{$basepath}/{$match}")) {
                    $minifier->add("{$basepath}/{$match}");
                }
            }
        }

        /** Ensure we can write file */
        if (is_writable(dirname($output))) {
            if ($type === 'js') {
                // js is mostly generally minified before.
                $minifier->concat($output);
            } else {
                $minifier->minify($output);
            }

            chmod($output, 0777);
        }
    }

    /**
     * @param $platform
     * @param $index_path
     * @param bool $css
     * @param bool $js
     */
    public function replaceIndex($platform, $index_path, $css = true, $js = true)
    {
        $source = $index_path;
        $dest = str_replace("index", "index-prod", $index_path);
        $css_file = str_replace("index.html", "dist/app.bundle-min.css", $index_path);

        $content = file_get_contents($source);

        $app_files = '';

        foreach (self::$EXCLUDE_CSS as $exclude) {
            $app_files .= '
        <link href="' . $exclude . '" rel="stylesheet" media="none" onload="if(media!=\'all\'){media=\'all\'}">';
        }

        /** Do not exclude js for browser. */
        if (($platform !== "browser") && ($platform !== "overview")) {
            foreach (self::$EXCLUDE_JS as $exclude) {
                $app_files .= '
        <script src="' . $exclude . '"></script>';
            }
            $file_js = "dist/app.bundle-min.js";
        } else {
            $file_js = "dist/app.bundle-min.js";
        }

        $current_release = __get("current_release");

        if ($css) {
            $content = preg_replace('/(\s*<(!--)?link href="[a-z0-9\.\/\-_]+\.css" rel="stylesheet"(--)?>\s*)+/mi', '', $content);
            $app_files .= '
        <style type="text/css">' . file_get_contents($css_file) . '</style>';
        }

        if ($js) {
            $content = preg_replace('/(\s*<(!--)?script[^>]+src="[a-z0-9\.\/\-_]+\.js"[^>]*><\/script(--)?>\s*)+/mi', '', $content);
            $app_files .= '
        <script src="' . $file_js . '?version=' . $current_release . '"></script>
        <script type="text/javascript">var cacheName = "pwa-worker-' . __get("current_release") . '";</script>';
        }

        $app_files .= '
    </head>';

        $content = preg_replace('/<\/head>/mi', $app_files . "\n\t", $content);

        File::putContents($dest, $content);
        if (file_exists($dest)) {
            chmod($dest, 0777);
        }

    }

    /**
     * Hook to clear cache
     */
    public static function clearCache()
    {
        $files_to_unlink = [];

        foreach (self::$PLATFORMS as $platform => $path) {
            $css = self::$PLATFORMS[$platform]['output_css'];
            $js = self::$PLATFORMS[$platform]['output_js'];
            $index = str_replace('index', 'index-prod', self::$PLATFORMS[$platform]['index']);

            $files_to_unlink[] = self::$basepath . $css;
            $files_to_unlink[] = self::$basepath . $js;
            $files_to_unlink[] = $index;
        }

        foreach ($files_to_unlink as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
}
