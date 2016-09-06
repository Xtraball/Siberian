<?php

include_once __DIR__ . '/../Minify/Exception.php';
include_once __DIR__ . '/../Minify/Minify.php';
include_once __DIR__ . '/../Minify/CSS.php';
include_once __DIR__ . '/../Minify/JS.php';
include_once __DIR__ . '/../Minify/Exceptions/BasicException.php';
include_once __DIR__ . '/../Minify/Exceptions/FileImportException.php';
include_once __DIR__ . '/../Minify/Exceptions/IOException.php';
include_once __DIR__ . '/../Minify/Converter.php';


/**
 *
 */
class Siberian_Minify extends \Minify\Minify {

    public static $EXCLUDE_CSS = array();
    public static $EXCLUDE_JS = array();

    public static $PLATFORMS = array(
        "browser" => array(
            "css" => true,
            "js" => true,
            "base" => "var/apps/browser",
            "index" => "var/apps/browser/index.html",
            "output_css" => "var/apps/browser/prod.css",
            "output_js" => "var/apps/browser/prod.js",
        ),
        "android" => array(
            "css" => false,
            "js" => false,
            "base" => "var/apps/ionic/android/assets/www",
            "index" => "var/apps/ionic/android/assets/www/index.html",
            "output_css" => "var/apps/ionic/android/assets/www/prod.css",
            "output_js" => "var/apps/ionic/android/assets/www/prod.js",
        ),
        "ios" => array(
            "css" => false,
            "js" => false,
            "base" => "var/apps/ionic/ios/www",
            "index" => "var/apps/ionic/ios/www/index.html",
            "output_css" => "var/apps/ionic/ios/www/prod.css",
            "output_js" => "var/apps/ionic/ios/www/prod.js",
        ),
        "ios-noads" => array(
            "css" => false,
            "js" => false,
            "base" => "var/apps/ionic/ios-noads/www",
            "index" => "var/apps/ionic/ios-noads/www/index.html",
            "output_css" => "var/apps/ionic/ios-noads/www/prod.css",
            "output_js" => "var/apps/ionic/ios-noads/www/prod.js",
        ),
    );

    public static $ASSETS = array();
    public static $ASSETS_CACHE = "var/cache/assets.cache";

    public static $basepath;

    public static $instance = null;


    public function __construct() {
        if(is_null(self::$instance)) {
            self::$basepath = Core_Model_Directory::getBasePathTo("");

            foreach(self::$PLATFORMS as $platform => $path) {
                $basepath = self::$basepath;
                self::$PLATFORMS[$platform]["index"] = "{$basepath}{$path['index']}";
            }

            if(Zend_Registry::isRegistered('config')) {
                $config = Zend_Registry::get('config');
                if(!empty($config->siberian->minify)) {
                    foreach($config->siberian->minify as $platform => $properties) {
                        if(isset(self::$PLATFORMS[$platform])) {
                            foreach($properties as $key => $property) {
                                if(isset(self::$PLATFORMS[$platform][$key])) {
                                    self::$PLATFORMS[$platform][$key] = ($property == 1);
                                }
                            }
                        }
                    }
                }
            }

            self::$instance = $this;
        }

        return self::$instance;
    }

    /** Hook for platforms */
    public static function addPlatform($name, $options = array()) {
        if(!isset(self::$PLATFORMS[$name])) {
            self::$PLATFORMS[$name] = $options;
        }
    }

    public function execute($path = null)
    {
        parent::execute($path);
    }

    public function build() {
        foreach(self::$PLATFORMS as $platform => $path) {
            $do_css = self::$PLATFORMS[$platform]['css'];
            $do_js = self::$PLATFORMS[$platform]['js'];
            $index_path = self::$PLATFORMS[$platform]['index'];
            $output_css = self::$PLATFORMS[$platform]['output_css'];
            $output_js = self::$PLATFORMS[$platform]['output_js'];

            /** Build only if files are not already cached */
            if($do_css && !is_readable($output_css)) {
                $this->minifyCss($platform, $index_path, $output_css);
            }
            if($do_js && !is_readable($output_js)) {
                $this->minifyJs($platform, $index_path, $output_js);
            }

            $this->replaceIndex($index_path, $do_css, $do_js);
        }


    }

    /**
     * @param $platform
     * @param $index_path
     * @param $output_css
     */
    public function minifyCss($platform, $index_path, $output_css) {
        $regex = '/<link href="([a-z0-9\.\/\-_]+\.css)" rel="stylesheet">/mi';

        $this->_minify("css", $regex, $index_path, $output_css);
    }

    /**
     * @param $platform
     * @param $index_path
     * @param $output_js
     */
    public function minifyJs($platform, $index_path, $output_js) {
        $regex = '/<script src="([a-z0-9\.\/\-_]+\.js)">/mi';

        $this->_minify("js", $regex, $index_path, $output_js);
    }

    /**
     * @param null $type
     * @param $regex
     * @param $content
     * @param $output
     */
    private function _minify($type, $regex, $content, $output) {
        if(!is_readable($content)) {
            return;
        }
        $index_content = file_get_contents($content);

        $basepath = dirname($content);

        switch($type) {
            case "css":
                    $minifier = new Minify\CSS();
                    $minifier->setMaxImportSize(5000);
                    $exclude = self::$EXCLUDE_CSS;
                break;
            case "js":
                    $minifier = new Minify\JS();
                    $exclude = self::$EXCLUDE_JS;
                break;
        }


        $matches = array();
        /** Match all css */
        if(preg_match_all($regex, $index_content, $matches)) {
            foreach($matches[1] as $match) {
                if(!in_array($match, $exclude)) {
                    $minifier->add("{$basepath}/{$match}");
                }
            }

        }

        /** Ensure we can write file */
        if(is_writable(dirname($output))) {
            $minifier->minify($output);
            chmod($output, 0777);
        }
    }

    /**
     * @param $index_path
     */
    public function replaceIndex($index_path, $css = true, $js = true) {
        $source = $index_path;
        $dest = str_replace("index", "index-prod", $index_path);

        $content = file_get_contents($source);

        $app_files = '</title>';

        if($css) {
            $content = preg_replace('/(\s*<(!--)?link href="[a-z0-9\.\/\-_]+\.css" rel="stylesheet"(--)?>\s*)+/mi', '', $content);
            $app_files .= '
        <link href="prod.css" rel="stylesheet">';
        }

        if($js) {
            $content = preg_replace('/(\s*<(!--)?script src="[a-z0-9\.\/\-_]+\.js"><\/script(--)?>\s*)+/mi', '', $content);
            $app_files .= '
        <script src="prod.js"></script>';
        }

        foreach(self::$EXCLUDE_CSS as $exclude) {
            $app_files .= '
        <link href="'.$exclude.'" rel="stylesheet">';
        }

        foreach(self::$EXCLUDE_JS as $exclude) {
            $app_files .= '
        <script src="'.$exclude.'"></script>';
        }

        $content = preg_replace('/<\/title>/mi', $app_files."\n\t", $content);

        file_put_contents($dest, $content);
        if(file_exists($dest)) {
            chmod($dest, 0777);
        }

    }

    /**
     * Hook to clear cache
     */
    public static function clearCache() {
        $files_to_unlink = array();

        foreach(self::$PLATFORMS as $platform => $path) {
            $css = self::$PLATFORMS[$platform]['output_css'];
            $js = self::$PLATFORMS[$platform]['output_js'];
            $index = str_replace("index", "index-prod", self::$PLATFORMS[$platform]['index']);

            $files_to_unlink[] = self::$basepath.$css;
            $files_to_unlink[] = self::$basepath.$js;
            $files_to_unlink[] = $index;
        }

        foreach($files_to_unlink as $file) {
            if(file_exists($file)) {
                unlink($file);
            }
        }
    }
}