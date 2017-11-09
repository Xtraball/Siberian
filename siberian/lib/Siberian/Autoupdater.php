<?php

/**
 * Class Siberian_Autoupdater
 *
 * @version 4.4.0
 *
 */

class Siberian_Autoupdater {
    /**
     * @var string
     */
    public static $manifest_json = "chcp.json";

    /**
     * @var string
     */
    public static $manifest_name = "chcp.manifest";

    /**
     * @var string
     */
    //public static $pwa_worker_files = "pwa-worker-template.js";

    /**
     * @var string
     */
    //public static $pwa_manifest = "pwa-manifest.json";

    /**
     * @param $host
     */
    public static function configure($host) {

        $current_release = "".Siberian_Version::VERSION.".".time();
        System_Model_Config::setValueFor("current_release", $current_release);

        # Clear
        Siberian_Cache_Design::clearCache();
        Siberian_Cache_Design::init();

        # Clear tmp (web app manifest, and temporary archives)
        Siberian_Cache::__clearTmp();

        # Rebuild index
        Siberian_Assets::copyAllAssets();
        Siberian_Assets::buildIndex();

        # Siberian Translations
        Siberian_Cache_Translation::clearCache();
        Siberian_Cache_Translation::init();

        # Rebuild minified
        $minifier = new Siberian_Minify();
        Siberian_Minify::clearCache();
        $minifier->build();

        self::manifest($host);
    }

    /**
     * CHCP Manifest builder
     *
     * @param $host
     */
    public static function manifest($host) {

        foreach(Siberian_Assets::$platforms as $type => $platforms) {

            foreach($platforms as $platform) {
                $www_folder = Siberian_Assets::$www[$type];
                $path = Core_Model_Directory::getBasePathTo($platform);
                $json_path = __ss($path.$www_folder.self::$manifest_json);
                $manifest_path = __ss($path.$www_folder.self::$manifest_name);

                $hash = array();
                $static_assets = array();

                /** Looping trough files */
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($path.$www_folder, 4096),
                    RecursiveIteratorIterator::SELF_FIRST);
                foreach($files as $file) {
                    if($file->isDir()) {
                        continue;
                    }

                    $pathname = $file->getPathname();
                    $relative_path = str_replace($path.$www_folder, "", $pathname);

                    # Add only required files
                    if(!self::exclude($relative_path)) {
                        $static_assets[] = $relative_path;
                        $hash[] = array(
                            "file" => $relative_path,
                            "hash" => md5_file($pathname),
                        );
                    }

                }

                $manifest = Siberian_Json::encode($hash, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

                file_put_contents($manifest_path, $manifest);

                # Release version change
                $release = array(
                    "content_url"           => $host.__ss($platform.$www_folder),
                    "min_native_interface"  => Siberian_Version::NATIVE_VERSION,
                    "release"               => System_Model_Config::getValueFor("current_release"),
                );

                $release = Siberian_Json::encode($release, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

                file_put_contents($json_path, $release);

                # Editing config.xml path
                if(isset(Siberian_Assets::$config_xml[$type])) {
                    $confix_xml_path = $path.Siberian_Assets::$config_xml[$type];
                    $path = $host.__ss($platform.$www_folder.self::$manifest_json);
                    __replace(
                        array(
                            '~(<config-file url=").*(" />)~i' => '$1'.$path.'$2',
                        ),
                        $confix_xml_path,
                        true
                    );
                }
            }

        }
    }

    /**
     * Test if file matches one of the exclude pattern
     *
     * @param $file
     * @return bool
     */
    public static function exclude($file) {
        foreach(Siberian_Assets::$exclude_files as $pattern) {
            if(preg_match("#".$pattern."#i", $file)) {
                return true;
            }
        }
        return false;
    }
}
