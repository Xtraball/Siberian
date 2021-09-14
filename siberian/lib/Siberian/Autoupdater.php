<?php

namespace Siberian;

use Siberian\Cache\Design;
use Siberian\Cache\Translation;

/**
 * Class \Siberian\Autoupdater
 *
 * @version 4.20.7
 *
 */

class Autoupdater
{
    /**
     * @param $host
     * @throws \Exception
     */
    public static function configure()
    {
        $current_release = Version::VERSION . '.' . time();
        __set('current_release', $current_release);

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
    }

    /**
     * Test if file matches one of the exclude pattern
     *
     * @param $file
     * @return bool
     */
    public static function exclude($file): bool
    {
        foreach (Assets::$exclude_files as $pattern) {
            if (preg_match("#" . $pattern . "#i", $file)) {
                return true;
            }
        }
        return false;
    }
}
