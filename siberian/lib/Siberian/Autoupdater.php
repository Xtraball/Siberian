<?php

namespace Siberian;

use Siberian\Cache\Design;
use Siberian\Cache\Translation;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

/**
 * Class \Siberian\Autoupdater
 *
 * @version 4.16.0
 *
 */

class Autoupdater
{
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
     * @throws \Exception
     */
    public static function configure($host)
    {
        $current_release = "" . Version::VERSION . "." . time();
        __set("current_release", $current_release);

        # Clear
        Design::clearCache();
        Design::init();

        # Clear tmp (web app manifest, and temporary archives)
        Cache::__clearTmp();

        # Rebuild index
        Assets::copyAllAssets();
        Assets::buildIndex();

        # Siberian Translations
        Translation::clearCache();
        Translation::init();

        # Rebuild minified
        $minifier = new Minify();
        Minify::clearCache();
        $minifier->build();

        self::manifest($host);
    }

    /**
     * @param $host
     * @throws \Exception
     */
    public static function manifest($host)
    {
        foreach (Assets::$platforms as $type => $platforms) {

            foreach ($platforms as $platform) {
                $www_folder = Assets::$www[$type];
                $path = path($platform);
                $json_path = __ss($path . $www_folder . self::$manifest_json);
                $manifest_path = __ss($path . $www_folder . self::$manifest_name);

                $hash = [];
                $static_assets = [];

                /** Looping trough files */
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($path . $www_folder, 4096),
                    RecursiveIteratorIterator::SELF_FIRST);
                foreach ($files as $file) {
                    if ($file->isDir()) {
                        continue;
                    }

                    $pathname = $file->getPathname();
                    $relative_path = str_replace($path . $www_folder, "", $pathname);

                    # Add only required files
                    if (!self::exclude($relative_path)) {
                        $static_assets[] = $relative_path;
                        $hash[] = [
                            "file" => $relative_path,
                            "hash" => md5_file($pathname),
                        ];
                    }

                }

                $manifest = Json::encode($hash, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

                File::putContents($manifest_path, $manifest);

                # Release version change
                $release = [
                    "content_url" => $host . __ss($platform . $www_folder),
                    "min_native_interface" => Version::NATIVE_VERSION,
                    "release" => __get("current_release"),
                ];

                $release = Json::encode($release, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

                File::putContents($json_path, $release);

                # Editing config.xml path
                if (isset(Assets::$config_xml[$type])) {
                    $confix_xml_path = $path . Assets::$config_xml[$type];
                    $path = $host . __ss($platform . $www_folder . self::$manifest_json);
                    __replace(
                        [
                            '~(<config-file url=").*(" />)~i' => '$1' . $path . '$2',
                        ],
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
    public static function exclude($file)
    {
        foreach (Assets::$exclude_files as $pattern) {
            if (preg_match("#" . $pattern . "#i", $file)) {
                return true;
            }
        }
        return false;
    }
}
