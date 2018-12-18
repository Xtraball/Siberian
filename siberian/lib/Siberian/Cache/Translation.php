<?php

/**
 * Class Siberian_Cache_Translation
 *
 * @version 4.15.9
 *
 * Adding inheritance system in the translations
 */

class Siberian_Cache_Translation extends Siberian_Cache implements Siberian_Cache_Interface
{
    /**
     *
     */
    const CODE = "translation";
    /**
     *
     */
    const CACHE_PATH = "var/cache/translation.cache";
    /**
     *
     */
    const CACHING = true;

    /**
     * @param $version
     * @param null $cache
     * @return mixed|void
     */
    public static function fetch($version, $cache = null)
    {
        $version = Core_Model_Directory::getBasePathTo("{$version}modules/");

        $module_folders = new DirectoryIterator("$version");

        $cache = static::getCache();

        /** Translations */
        foreach ($module_folders as $module_folder) {
            if ($module_folder->isDir() && !$module_folder->isDot() && is_readable("{$module_folder->getPathname()}/resources/translations/")) {

                $modules_translations = new DirectoryIterator("{$module_folder->getPathname()}/resources/translations/");

                foreach ($modules_translations as $modules_translation) {
                    if ($modules_translation->isDir() && !$modules_translation->isDot()) {
                        /** Init the array if not. */
                        $language = $modules_translation->getFilename();
                        if (!isset($cache[$language])) {
                            $cache[$language] = [];
                        }

                        /** Looping trough files */
                        $files = new DirectoryIterator($modules_translation->getPathname());
                        foreach ($files as $file) {
                            if ($file->getExtension() === "csv") {
                                $basename = $file->getFilename();
                                if (!isset($cache[$language][$basename])) {
                                    $cache[$language][$basename] = $file->getPathname();
                                }
                            }

                            if ($file->getExtension() === "list") {
                                $cache["mobile_list"][] = $file->getPathname();
                            }
                        }
                    }
                }
            }
        }

        static::setCache($cache);
    }

    /**
     * @return mixed|void
     */
    public static function preWalk()
    {
        $languages = Core_Model_Directory::getBasePathTo("languages");

        $cache = static::getCache();

        $translations = new DirectoryIterator("{$languages}");

        foreach ($translations as $translation) {
            if ($translation->isDir() && !$translation->isDot()) {
                /** Init the array if not. */
                $language = $translation->getFilename();
                if (!isset($cache[$language])) {
                    $cache[$language] = [];
                }

                /** Looping trough files */
                $files = new DirectoryIterator($translation->getPathname());
                foreach ($files as $file) {
                    if ($file->getExtension() == "csv") {
                        $basename = $file->getFilename();
                        if (!isset($cache[$language][$basename])) {
                            $cache[$language][$basename] = $file->getPathname();
                        }
                    }

                    if ($file->getExtension() == "list") {
                        $cache["mobile_list"][] = $file->getPathname();
                    }
                }
            }
        }

        static::setCache($cache);
    }

    /**
     * Common method for TYPE walkers
     *
     * We refresh only local cache, sae/mae/pe are pre-built for convenience.
     */
    public static function walk()
    {
        switch(\Siberian_Version::VERSION) {
            case "PE":
                static::fetch(self::SAE_PATH);
                static::fetch(self::MAE_PATH);
                static::fetch(self::PE_PATH);
                break;
            case "MAE":
                static::fetch(self::SAE_PATH);
                static::fetch(self::MAE_PATH);
                break;
            case "SAE": default:
                static::fetch(self::SAE_PATH);
                break;
        }
        static::fetch(self::LOCAL_PATH);
    }

    /**
     * @return mixed|void
     */
    public static function postWalk()
    {

    }
}
