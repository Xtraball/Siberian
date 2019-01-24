<?php

use Gettext\Translator;
use Gettext\Translation;
use Gettext\Translations;
use Gettext\Merge;

/**
 * Class Core_Model_Translator
 */
class Core_Model_Translator
{
    /**
     * @var Gettext\Translator
     */
    public static $_translator;

    /**
     * @var Gettext\Translations
     */
    public static $_translations;

    /**
     * @var string
     */
    public static $_currentLanguage;

    /**
     *
     */
    public static function init()
    {
        self::$_translator = new Translator();
        self::$_translator->register();

        self::$_translations = new Translations();
        self::$_translations->setLanguage("en");

        // Load `base` english
        self::loadKeys();
    }

    /**
     *
     */
    public static function loadKeys()
    {
        $files = Siberian_Cache_Translation::getCache();
        $baseFiles = $files["base"];
        foreach ($baseFiles as $file) {
            self::$_translations->addFromMoFile($file);
        }

        // Then load into the translator!
        self::$_translator->loadTranslations(self::$_translations);
    }

    /**
     * @param null|string $overrideLang
     */
    public static function loadDefaultsAndUser($overrideLang = null)
    {
        $translations = new Translations();

        $currentLanguage = ($overrideLang === null) ? Core_Model_Language::getCurrentLanguage() : $overrideLang;
        $translations->setLanguage($currentLanguage);

        $files = Siberian_Cache_Translation::getCache();
        $currentDefaults = $files[$currentLanguage];
        foreach ($currentDefaults as $file) {
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            switch ($extension) {
                case "csv";
                    $translations->addFromCsvDictionaryFile($file, ["delimiter" => ";"]);
                    break;
                case "mo":
                    $translations->addFromMoFile($file);
                    break;
            }
        }

        self::$_translations->mergeWith($translations, Merge::ADD | Merge::TRANSLATION_OVERRIDE);

        // Load user translation files!
        $userTranslations = new Translations();
        $userTranslations->setLanguage($currentLanguage);

        $userTranslationFolder = Core_Model_Directory::getBasePathTo("/languages/{$currentLanguage}");
        if (is_dir($userTranslationFolder)) {
            $files = new DirectoryIterator($userTranslationFolder);
            foreach ($files as $file) {
                switch ($file->getExtension()) {
                    case "csv";
                        $userTranslations->addFromCsvDictionaryFile($file->getPathname(), ["delimiter" => ";"]);
                        break;
                    case "mo":
                        $userTranslations->addFromMoFile($file->getPathname());
                        break;
                }
            }
        }

        self::$_translations->mergeWith($userTranslations, Merge::ADD | Merge::TRANSLATION_OVERRIDE);

        // Then load into the translator!
        self::$_translator->loadTranslations(self::$_translations);
    }

    /**
     * @deprecated
     *
     * @param null $platform
     * @return array|mixed
     * @throws Zend_Translate_Exception
     */
    public static function getTranslationsFor($platform = null)
    {
        return self::_getIonicTranslations();
    }

    /**
     * @deprecated
     *
     * @param $moduleName
     */
    public static function prepare($moduleName)
    {
        // Do nothing!
    }


    /**
     * @param $moduleName
     */
    public static function addModule($moduleName)
    {
        // Deprecated do nothing!
    }

    /**
     *
     * @deprecated
     *
     * @param $text
     * @param array $args
     * @return mixed
     */
    public static function translate($text, array $args = [])
    {
        return call_user_func_array("__", func_get_args());
    }

    /**
     * @return array|mixed
     * @throws Zend_Translate_Exception
     */
    protected static function _getIonicTranslations()
    {
        $translation_cache = Siberian_Cache_Translation::getCache();
        $mobileFiles = $translation_cache["mobile_list"];

        $keys = [];
        foreach ($mobileFiles as $mobileFile) {
            $resource = fopen($mobileFile, "r");
            while ($data = fgetcsv($resource, 1024, ";", "\"")) {
                if (!empty($data[0]) && stripos($data[0], "%s") === false) {
                    $keys[] = $data[0];
                }
            }
        }

        // Ensure unique keys
        $keys = array_unique($keys);

        $allTranslations = [];
        $allFilesTranslations = self::parseTranslations(Core_Model_Language::getCurrentLanguage());

        foreach ($allFilesTranslations as $file => $translations) {
            $allTranslations = array_merge($allTranslations, $translations);
        }

        $translations = array_filter(array_intersect_key($allTranslations, array_flip($keys)));

        return $translations;
    }

    /**
     * @param $langId
     * @return array
     * @throws Zend_Translate_Exception
     */
    public static function parseTranslations($langId)
    {
        $translations = [];
        $userTranslationsDirectory = Core_Model_Directory::getBasePathTo("languages/{$langId}/");

        $cachedFiles = Siberian_Cache_Translation::getCache();

        $base = $cachedFiles["base"];
        $default = $cachedFiles["default"];

        foreach ($default as $filename => $path) {
            $moVariant = str_replace(".csv", ".mo", $filename);

            // Re-inject old "default" files if they are still using old .csv format!
            if (!array_key_exists($moVariant, $base)) {
                $base[$filename] = $path;
            }
        }

        // $tmpTranslationData
        $tmpTranslationData = [];

        // Base
        foreach ($base as $filename => $path) {
            if (!is_file($path)) {
                continue;
            }

            $pathinfo = pathinfo($path);
            $type = $pathinfo["extension"];
            if (!in_array($type, ["csv", "mo"])) {
                continue;
            }

            // Easy
            $tmpTranslationData = self::parseType($tmpTranslationData, $filename, $path, $type);

            if (!empty($tmpTranslationData)) {
                $translations = array_merge($translations, $tmpTranslationData);
            }
        }

        // Defaults values
        foreach ($base as $filename => $path) {
            if (!is_file($path)) {
                continue;
            }

            $pathinfo = pathinfo($path);
            $type = $pathinfo["extension"];
            $fileBase = basename($filename, ".{$type}");
            if (!in_array($type, ["csv", "mo"])) {
                continue;
            }

            // Default translation (if exists) mixed csv/mo, mo being more recent!
            $defaultTranslationCSV = $cachedFiles[$langId]["{$fileBase}.csv"];
            if (is_file($defaultTranslationCSV)) {
                $tmpTranslationData = self::parseType($tmpTranslationData, $filename, $defaultTranslationCSV, "csv");
            }
            $defaultTranslationMO = $cachedFiles[$langId]["{$fileBase}.mo"];
            if (is_file($defaultTranslationMO)) {
                $tmpTranslationData = self::parseType($tmpTranslationData, $filename, $defaultTranslationMO, "mo");
            }

            if (!empty($tmpTranslationData)) {
                $translations = array_merge($translations, $tmpTranslationData);
            }
        }

        // User
        foreach ($base as $filename => $path) {
            if (!is_file($path)) {
                continue;
            }

            $pathinfo = pathinfo($path);
            $type = $pathinfo["extension"];
            $fileBase = basename($filename, ".{$type}");
            if (!in_array($type, ["csv", "mo"])) {
                continue;
            }

            // User translations (if exists)!
            $userTranslationMO = $userTranslationsDirectory . $fileBase . ".mo";
            $userTranslationCSV = $userTranslationsDirectory . $fileBase . ".csv";

            if (is_file($userTranslationMO)) {
                $tmpTranslationData = self::parseType($tmpTranslationData, $filename, $userTranslationMO, "mo");
            } else if (is_file($userTranslationCSV)) {
                $tmpTranslationData = self::parseType($tmpTranslationData, $filename, $userTranslationCSV, "csv");
            }

            if (!empty($tmpTranslationData)) {
                $translations = array_merge($translations, $tmpTranslationData);
            }
        }

        return $translations;
    }

    /**
     * @param $tmpTranslationData
     * @param $filename
     * @param $path
     * @param $type
     * @return mixed
     * @throws Zend_Translate_Exception
     */
    public static function parseType($tmpTranslationData, $filename, $path, $type)
    {
        // Gettext / CSV selector! MsgID!
        if (!array_key_exists($filename, $tmpTranslationData)) {
            $tmpTranslationData[$filename] = [];
        }

        switch ($type) {
            case "csv":
                $userTranslations = new Translations();
                $userTranslations->addFromCsvDictionaryFile($path, ["delimiter" => ";"]);
                foreach ($userTranslations as $userTranslation) {
                    $key = str_replace('\"', '"', $userTranslation->getOriginal());
                    $value = trim(str_replace('\"', '"', $userTranslation->getTranslation()));

                    if (strlen($value) > 0) {
                        if (!array_key_exists($key, $tmpTranslationData[$filename])) {
                            $tmpTranslationData[$filename][$key] = null;
                        }
                        $tmpTranslationData[$filename][$key] = $value;
                    }
                }

                break;
            case "mo":
                $userTranslations = new Translations();
                $userTranslations->addFromMoFile($path);
                foreach ($userTranslations as $userTranslation) {
                    $key = str_replace('\"', '"', $userTranslation->getOriginal());
                    $value = trim(str_replace('\"', '"', $userTranslation->getTranslation()));

                    if (strlen($value) > 0) {
                        if (!array_key_exists($key, $tmpTranslationData[$filename])) {
                            $tmpTranslationData[$filename][$key] = null;
                        }
                        $tmpTranslationData[$filename][$key] = $value;
                    }
                }

                break;
        }

        return array_filter($tmpTranslationData);
    }

    /**
     * @param $langId
     * @return array
     * @throws Zend_Translate_Exception
     */
    public static function parseTranslationsForBackoffice($langId)
    {
        $translations = [];
        $userTranslationsDirectory = Core_Model_Directory::getBasePathTo("languages/{$langId}/");

        $cachedFiles = Siberian_Cache_Translation::getCache();

        $base = $cachedFiles["base"];
        $default = $cachedFiles["default"];

        foreach ($default as $filename => $path) {
            $moVariant = str_replace(".csv", ".mo", $filename);

            // Re-inject old "default" files if they are still using old .csv format!
            if (!array_key_exists($moVariant, $base)) {
                $base[$filename] = $path;
            }
        }

        // $tmpTranslationData
        $tmpTranslationData = [];

        // Base
        foreach ($base as $filename => $path) {
            if (!is_file($path)) {
                continue;
            }

            $pathinfo = pathinfo($path);
            $type = $pathinfo["extension"];
            if (!in_array($type, ["csv", "mo"])) {
                continue;
            }

            // Easy
            $tmpTranslationData = self::parseTypeForBackoffice($tmpTranslationData, $filename, $path, $type, "original");

            if (!empty($tmpTranslationData)) {
                $translations = array_merge($translations, $tmpTranslationData);
            }
        }

        // Defaults values
        foreach ($base as $filename => $path) {
            if (!is_file($path)) {
                continue;
            }

            $pathinfo = pathinfo($path);
            $type = $pathinfo["extension"];
            $fileBase = basename($filename, ".{$type}");
            if (!in_array($type, ["csv", "mo"])) {
                continue;
            }

            // Default translation (if exists) mixed csv/mo, mo being more recent!
            $defaultTranslationCSV = $cachedFiles[$langId]["{$fileBase}.csv"];
            if (is_file($defaultTranslationCSV)) {
                $tmpTranslationData = self::parseTypeForBackoffice($tmpTranslationData, $filename, $defaultTranslationCSV, "csv", "default");
            }
            $defaultTranslationMO = $cachedFiles[$langId]["{$fileBase}.mo"];
            if (is_file($defaultTranslationMO)) {
                $tmpTranslationData = self::parseTypeForBackoffice($tmpTranslationData, $filename, $defaultTranslationMO, "mo", "default");
            }

            if (!empty($tmpTranslationData)) {
                $translations = array_merge($translations, $tmpTranslationData);
            }
        }

        // User
        foreach ($base as $filename => $path) {
            if (!is_file($path)) {
                continue;
            }

            $pathinfo = pathinfo($path);
            $type = $pathinfo["extension"];
            $fileBase = basename($filename, ".{$type}");
            if (!in_array($type, ["csv", "mo"])) {
                continue;
            }

            // User translations (if exists)!
            $userTranslationCSV = $userTranslationsDirectory . $fileBase . ".csv";
            $userTranslationMO = $userTranslationsDirectory . $fileBase . ".mo";

            if (is_file($userTranslationMO)) {
                $tmpTranslationData = self::parseTypeForBackoffice($tmpTranslationData, $filename, $userTranslationMO, "mo", "user");
            } else if (is_file($userTranslationCSV)) {
                $tmpTranslationData = self::parseTypeForBackoffice($tmpTranslationData, $filename, $userTranslationCSV, "csv", "user");
            }

            if (!empty($tmpTranslationData)) {
                $translations = array_merge($translations, $tmpTranslationData);
            }
        }

        return $translations;
    }

    /**
     * @param $tmpTranslationData
     * @param $filename
     * @param $path
     * @param $type
     * @param string $fillKey
     * @return mixed
     * @throws Zend_Translate_Exception
     */
    public static function parseTypeForBackoffice($tmpTranslationData, $filename, $path, $type, $fillKey = "user")
    {
        // Gettext / CSV selector! MsgID!
        if (!array_key_exists($filename, $tmpTranslationData)) {
            $tmpTranslationData[$filename] = [];
        }

        switch ($type) {
            case "csv":
                $csvResource = fopen($path, "r");
                while ($line = fgetcsv($csvResource, 1024, ";", '"')) {
                    $key = str_replace('\"', '"', $line[0]);
                    if (!isset($tmpTranslationData[$filename][$key])) {
                        $tmpTranslationData[$filename][$key] = [
                            "context" => null,
                            "original" => $key,
                            "default" => null,
                            "user" => null,
                        ];
                    }
                    if (isset($line[1]) && !empty($line[1])) {
                        $tmpTranslationData[$filename][$key][$fillKey] = str_replace('\"', '"', $line[1]);
                    }

                }
                fclose($csvResource);

                break;
            case "mo":
                $userTranslations = new Translations();
                $userTranslations->addFromMoFile($path);
                foreach ($userTranslations as $userTranslation) {
                    $key = str_replace('\"', '"', $userTranslation->getOriginal());
                    if (!isset($tmpTranslationData[$filename][$key])) {
                        $tmpTranslationData[$filename][$key] = [
                            "context" => $userTranslation->getContext(),
                            "original" => $key,
                            "default" => null,
                            "user" => null,
                        ];
                    }

                    $value = str_replace('\"', '"', $userTranslation->getTranslation());
                    if (!empty($value)) {
                        $tmpTranslationData[$filename][$key][$fillKey] = $value;
                    }
                }

                break;
        }

        return $tmpTranslationData;
    }
}
