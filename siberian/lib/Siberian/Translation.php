<?php

namespace Siberian;

/**
 * Class Translation
 * @package Siberian
 */
class Translation
{
    /**
     * Compare existing translations with all __() / ->_() calls and extract missing!
     *
     * @param null $module
     * @param bool $context
     */
    public static function extractAll ($module = null, $context = false)
    {
        $allKeys = [];

        $languages = \Core_Model_Directory::getBasePathTo('/languages/default');
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($languages, 4096),
            \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($files as $file) {
            if ($file->isDir()) {
                continue;
            }

            $extension = pathinfo($file->getFilename(), PATHINFO_EXTENSION);
            if ($extension === 'csv') {
                if (($handle = fopen($file->getPathname(), 'r')) !== false) {
                    while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                        $num = count($data);
                        for ($c=0; $c < $num; $c++) {
                            if (!in_array($data[$c], $allKeys)) {
                                $allKeys[] = $data[$c];
                            }
                        }
                    }
                    fclose($handle);
                }
            }
        }

        $languagesModules = \Core_Model_Directory::getBasePathTo('/app');
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($languagesModules, 4096),
            \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($files as $file) {
            if ($file->isDir()) {
                continue;
            }

            $extension = pathinfo($file->getFilename(), PATHINFO_EXTENSION);
            if ($extension === 'csv' &&
                strpos($file->getPathname(), '/default/') !== false) {
                if (($handle = fopen($file->getPathname(), 'r')) !== false) {
                    while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                        $num = count($data);
                        for ($c=0; $c < $num; $c++) {
                            if (!in_array($data[$c], $allKeys)) {
                                $allKeys[] = $data[$c];
                            }
                        }
                    }
                    fclose($handle);
                }
            }
        }

        ini_set('pcre.backtrack_limit', 10000000000000000000);
        ini_set('pcre.recursion_limit', 10000000000000000000);

        $extractTranslate = [];

        if ($module !== null) {
            $appPath = \Core_Model_Directory::getBasePathTo("/app/local/modules/{$module}");
        } else {
            $appPath = \Core_Model_Directory::getBasePathTo('/app');
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($appPath, 4096),
            \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($files as $file) {
            if ($file->isDir()) {
                continue;
            }

            $extension = pathinfo($file->getFilename(), PATHINFO_EXTENSION);
            if (in_array($extension, ['php', 'phtml', 'csv'])) {
                $textContent = file_get_contents($file->getPathname());
                if ($context !== false) {
                    $count = preg_match_all('/((p__)\("([!\w\s\d\'<>\/\\\,~|°¨^?:;.%\-@#$€£&=+*(){}\[\]]+)(",|"\)))/mi', $textContent, $matches);
                } else {
                    $count = preg_match_all('/((__|->_|__js)\("([!\w\s\d\'<>\/\\\,~|°¨^?:;.%\-@#$€£&=+*(){}\[\]]+)(",|"\)))/mi', $textContent, $matches);
                }
                if ($count > 0) {
                    foreach ($matches[3] as $element) {
                        if (!in_array($element, $extractTranslate)) {
                            $extractTranslate[] = $element;
                        }
                    }
                }
                if ($context !== false) {
                    $count = preg_match_all('/((p__)\("([!\w\s\d\'<>\/\\\,~|°¨^?:;.%\-@#$€£&=+*(){}\[\]]+)(",|"\)))/mi', $textContent, $matches);
                } else {
                    $count = preg_match_all('/((__|->_|__js)\(\'([!\w\s\d"<>\/\\\,~|°¨^?:;.%\-@#$€£&=+*(){}\[\]]+)(\',|\'\)))/mi', $textContent, $matches);
                }

                if ($count > 0) {
                    foreach ($matches[3] as $element) {
                        if (!in_array($element, $extractTranslate)) {
                            $extractTranslate[] = $element;
                        }
                    }
                }
            }
        }

        foreach ($extractTranslate as $extract) {
            if (!in_array(addcslashes($extract, '"'), $allKeys)) {
                if ($context !== false) {
                    echo 'msgctxt "' . $context . '"' . PHP_EOL;
                }
                echo 'msgid "' . addcslashes($extract, '"') . '"' . PHP_EOL;
                echo 'msgstr "' . addcslashes($extract, '"') . '"' . PHP_EOL . PHP_EOL;
            }
        }
    }

    /**
     * @param $context
     * @param $module
     * @param $path
     */
    public static function registerExtractor ($context, $module, $path = null)
    {
        global $extractModules;
        $extractModules[$context] = [
            "module" => $module,
            "path" => $path,
        ];
    }
}