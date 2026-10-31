<?php

namespace Siberian\Locale;

use DirectoryIterator;

/**
 * Which catalog file a module contributes to the translation file cache.
 *
 * The first file registered for a language and a file name wins, later ones are ignored: the caller decides the order,
 * and the locales shipped in the core go last so that they never replace a translation a user or an installed pack
 * already provides for the same language.
 */
class Catalogs
{
    /**
     * Module folder of the locales shipped in the core (app/sae/modules/<name>).
     */
    const SHIPPED_MODULE = 'Locales';

    /**
     * @param string $modulePath absolute path of a module folder
     * @param array $cache language => [file name => path], "mobile_list" => [paths]
     * @return array the cache with what the module adds, nothing already there is replaced
     */
    public static function indexModule($modulePath, array $cache)
    {
        $translations = "{$modulePath}/resources/translations/";
        if (!is_readable($translations)) {
            return $cache;
        }

        foreach (new DirectoryIterator($translations) as $languageDir) {
            if (!$languageDir->isDir() || $languageDir->isDot()) {
                continue;
            }
            $language = $languageDir->getFilename();
            if (!isset($cache[$language])) {
                $cache[$language] = [];
            }

            foreach (new DirectoryIterator($languageDir->getPathname()) as $file) {
                $extension = $file->getExtension();
                // csv: old modules still using it
                if ($extension === 'csv' || $extension === 'po') {
                    $basename = $file->getFilename();
                    if (!isset($cache[$language][$basename])) {
                        $cache[$language][$basename] = $file->getPathname();
                    }
                }
                if ($extension === 'list') {
                    $cache['mobile_list'][] = $file->getPathname();
                }
            }
        }

        return $cache;
    }
}
