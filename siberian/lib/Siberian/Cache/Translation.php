<?php

/**
 * Class Siberian_Cache_Translation
 *
 * @version 4.2.0
 *
 * Adding inheritance system in the translations
 *
 */

class Siberian_Cache_Translation extends Siberian_Cache implements Siberian_Cache_Interface
{
    const CODE = "translation";
    const CACHE_PATH = "var/cache/translation.cache";
    const CACHING = true;

    public static function fetch($version) {
        $version = Core_Model_Directory::getBasePathTo("{$version}modules/");

        $module_folders = new DirectoryIterator("$version");

        $cache = static::getCache();

        /** Translations */
        foreach ($module_folders as $module_folder) {
            if($module_folder->isDir() && !$module_folder->isDot() && is_readable("{$module_folder->getPathname()}/resources/translations/")) {

                $modules_translations = new DirectoryIterator("{$module_folder->getPathname()}/resources/translations/");

                foreach ($modules_translations as $modules_translation) {
                    if($modules_translation->isDir() && !$modules_translation->isDot()) {
                        /** Init the array if not. */
                        $language = $modules_translation->getFilename();
                        if (!isset($cache[$language])) {
                            $cache[$language] = array();
                        }

                        /** Looping trough files */
                        $files = new DirectoryIterator($modules_translation->getPathname());
                        foreach($files as $file) {
                            if($file->getExtension() == "csv") {
                                $basename = $file->getFilename();
                                if(!isset($cache[$language][$basename])) {
                                    $cache[$language][$basename] = $file->getPathname();
                                }
                            }

                            if($file->getExtension() == "list") {
                                $cache["mobile_list"][] = $file->getPathname();
                            }
                        }
                    }
                }
            }
        }

        static::setCache($cache);
    }

    public static function preWalk() {
        $languages = Core_Model_Directory::getBasePathTo("languages");

        $cache = static::getCache();

        $translations = new DirectoryIterator("{$languages}");

        foreach ($translations as $translation) {
            if($translation->isDir() && !$translation->isDot()) {
                /** Init the array if not. */
                $language = $translation->getFilename();
                if (!isset($cache[$language])) {
                    $cache[$language] = array();
                }

                /** Looping trough files */
                $files = new DirectoryIterator($translation->getPathname());
                foreach($files as $file) {
                    if($file->getExtension() == "csv") {
                        $basename = $file->getFilename();
                        if(!isset($cache[$language][$basename])) {
                            $cache[$language][$basename] = $file->getPathname();
                        }
                    }

                    if($file->getExtension() == "list") {
                        $cache["mobile_list"][] = $file->getPathname();
                    }
                }
            }
        }

        static::setCache($cache);
    }

    public static function postWalk() {

    }
}
