<?php

use Gettext\Translator;
use Gettext\Translation;
use Gettext\Translations;
use Gettext\Merge;
use Siberian\Hook;

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
            if (!is_file($file)) {
                continue;
            }

            $extension = pathinfo($file, PATHINFO_EXTENSION);
            switch ($extension) {
                case "po":
                    self::$_translations->addFromPoFile($file);
                    break;
            }

        }

        // Then load into the translator!
        self::$_translator->loadTranslations(self::$_translations);
    }

    /**
     * @param null $overrideLang
     * @param null $application
     * @throws Zend_Exception
     */
    public static function loadDefaultsAndUser($overrideLang = null, $application = null)
    {
        $translations = new Translations();

        $currentLanguage = ($overrideLang === null) ? Core_Model_Language::getCurrentLanguage() : $overrideLang;
        $translations->setLanguage($currentLanguage);

        $files = Siberian_Cache_Translation::getCache();
        $currentDefaults = $files[$currentLanguage] ?? [];
        if (is_array($currentDefaults)) {
            foreach ($currentDefaults as $file) {
                if (!is_file($file)) {
                    continue; // skip missing files.
                }
                $extension = pathinfo($file, PATHINFO_EXTENSION);
                switch ($extension) {
                    case "csv";
                        $translations->addFromCsvDictionaryFile($file, ["delimiter" => ";"]);
                        break;
                    case "po":
                        $translations->addFromPoFile($file);
                        break;
                }
            }
        }

        self::$_translations->mergeWith($translations, Merge::ADD | Merge::TRANSLATION_OVERRIDE | Merge::FLAGS_THEIRS);

        $userTranslationFolder = Core_Model_Directory::getBasePathTo("/languages/{$currentLanguage}");
        $isLoaded = [];
        if (is_dir($userTranslationFolder)) {
            $files = new DirectoryIterator($userTranslationFolder);
            foreach ($files as $file) {
                $name = str_replace([".po", ".csv"], "", $file->getFilename());
                if (!in_array($name, $isLoaded) && !$file->isDot()) {
                    $isLoaded[] = $name;

                    // Load user translation files!
                    $userTranslations = new Translations();
                    $userTranslations->setLanguage($currentLanguage);

                    switch ($file->getExtension()) {
                        case "csv";
                            $poVariant = str_replace(".csv", ".po", $file->getPathname());
                            if (is_file($poVariant))  {
                                $userTranslations->addFromPoFile($poVariant);
                            } else {
                                $userTranslations->addFromCsvDictionaryFile($file->getPathname(), ["delimiter" => ";"]);
                            }
                            break;
                        case "po":
                            $userTranslations->addFromPoFile($file->getPathname());
                            break;
                    }

                    self::$_translations->mergeWith($userTranslations, Merge::ADD | Merge::TRANSLATION_OVERRIDE | Merge::FLAGS_THEIRS);
                }

            }
        }

        // Hook when user translations are ready!
        $payload = Hook::trigger('editor.translation.ready', [
            'application' => $application,
            'currentLanguage' => $currentLanguage,
            'translations' => self::$_translations
        ]);

        self::$_translations = $payload['translations'];

        // Then load into the translator!
        self::$_translator->loadTranslations(self::$_translations);
    }

    /**
     * @param null $platform
     * @return array|mixed
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
     */
    protected static function _getIonicTranslations()
    {
        $translation_cache = Siberian_Cache_Translation::getCache();

        // --Start deprecation .list--
        // @todo deprecate
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
        // @todo deprecate
        // --End deprecation .list--

        $allFilesTranslations = self::parseTranslations(Core_Model_Language::getCurrentLanguage());

        $withoutContextTranslations = [];
        $withContextTranslations = [];
        foreach ($allFilesTranslations as $file => $translations) {
            foreach ($translations as $key => $translation) {
                $context = $translation["context"];
                $flags = $translation["flags"];

                // Adding this new key
                $mobile = false;
                if (in_array("mobile", $flags)) {
                    $mobile = true;
                    $keys[] = $key;
                }

                // If we have no context, but STILL the flag is mobile, will add it.
                if (empty($context)) {
                    $withoutContextTranslations[$key] = $translation["value"];
                } else {
                    if ($mobile) {
                        if (!array_key_exists($context, $withContextTranslations)) {
                            $withContextTranslations[$context] = [];
                        }
                        $withContextTranslations[$context][$key] = $translation["value"];
                    }
                }

            }
        }

        // Ensure unique keys
        $keys = array_unique($keys);

        $translations = array_filter(array_intersect_key($withoutContextTranslations, array_flip($keys)));

        // New contextual values!
        $translations["_context"] = $withContextTranslations;

        return $translations;
    }

    /**
     * @param $langId
     * @return array
     */
    public static function parseTranslations($langId)
    {
        $translations = [];
        $userTranslationsDirectory = Core_Model_Directory::getBasePathTo("languages/{$langId}/");

        $cachedFiles = Siberian_Cache_Translation::getCache();

        $base = $cachedFiles["base"];
        $default = $cachedFiles["default"];

        foreach ($default as $filename => $path) {
            $poVariant = str_replace(".csv", ".po", $filename);

            // Re-inject old "default" files if they are still using old .csv format!
            if (!array_key_exists($poVariant, $base)) {
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
            if (!in_array($type, ["csv", "po"])) {
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
            if (!in_array($type, ["csv", "po"])) {
                continue;
            }

            // Default translation (if exists) mixed csv/mo, mo being more recent!
            $defaultTranslationCSV = $cachedFiles[$langId]["{$fileBase}.csv"];
            if (is_file($defaultTranslationCSV)) {
                $tmpTranslationData = self::parseType($tmpTranslationData, $filename, $defaultTranslationCSV, "csv");
            }
            $defaultTranslationPO = $cachedFiles[$langId]["{$fileBase}.po"];
            if (is_file($defaultTranslationPO)) {
                $tmpTranslationData = self::parseType($tmpTranslationData, $filename, $defaultTranslationPO, "po");
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
            if (!in_array($type, ["csv", "po"])) {
                continue;
            }

            // User translations (if exists)!
            $userTranslationPO = $userTranslationsDirectory . $fileBase . ".po";
            $userTranslationCSV = $userTranslationsDirectory . $fileBase . ".csv";

            if (is_file($userTranslationPO)) {
                $tmpTranslationData = self::parseType($tmpTranslationData, $filename, $userTranslationPO, "po");
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
     * @return array
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
                        $tmpTranslationData[$filename][$key] = [
                            "flags" => [],
                            "context" => null,
                            "value" => $value,
                        ];
                    }
                }

                break;
            case "po":
                $userTranslations = new Translations();
                $userTranslations->addFromPoFile($path);
                foreach ($userTranslations as $userTranslation) {
                    $key = str_replace('\"', '"', $userTranslation->getOriginal());
                    $value = trim(str_replace('\"', '"', $userTranslation->getTranslation()));

                    if (strlen($value) > 0) {
                        if (!array_key_exists($key, $tmpTranslationData[$filename])) {
                            $tmpTranslationData[$filename][$key] = null;
                        }
                        $tmpTranslationData[$filename][$key] = [
                            "flags" => $userTranslation->getFlags(),
                            "context" => $userTranslation->getContext(),
                            "value" => $value,
                        ];
                    }
                }

                break;
        }

        return array_filter($tmpTranslationData);
    }

    /**
     * @param $langId
     * @return array
     */
    public static function parseTranslationsForBackoffice($langId)
    {
        $translations = [];
        $userTranslationsDirectory = Core_Model_Directory::getBasePathTo("languages/{$langId}/");

        $cachedFiles = Siberian_Cache_Translation::getCache();

        $base = $cachedFiles['base'];
        $default = $cachedFiles['default'];

        foreach ($default as $filename => $path) {
            $poVariant = str_replace('.csv', '.po', $filename);

            // Re-inject old "default" files if they are still using old .csv format!
            if (!array_key_exists($poVariant, $base)) {
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
            $type = $pathinfo['extension'];
            if (!in_array($type, ['csv', 'po'])) {
                continue;
            }

            // Easy
            $tmpTranslationData = self::parseTypeForBackoffice($tmpTranslationData, $filename, $path, $type, 'original');

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
            $type = $pathinfo['extension'];
            $fileBase = basename($filename, ".{$type}");
            if (!in_array($type, ['csv', 'po'])) {
                continue;
            }

            // Default translation (if exists) mixed csv/po, po being more recent!
            if (array_key_exists($langId, $cachedFiles)) {
                $csvFile = "{$fileBase}.csv";
                if (array_key_exists($csvFile, $cachedFiles[$langId])) {
                    $defaultTranslationCSV = $cachedFiles[$langId][$csvFile];
                    if (is_file($defaultTranslationCSV)) {
                        $tmpTranslationData = self::parseTypeForBackoffice($tmpTranslationData, $filename, $defaultTranslationCSV, 'csv', 'default');
                    }
                }
                $poFile = "{$fileBase}.po";
                if (array_key_exists($poFile, $cachedFiles[$langId])) {
                    $defaultTranslationPO = $cachedFiles[$langId][$poFile];
                    if (is_file($defaultTranslationPO)) {
                        $tmpTranslationData = self::parseTypeForBackoffice($tmpTranslationData, $filename, $defaultTranslationPO, 'po', 'default');
                    }
                }
            }

            if (!empty($tmpTranslationData)) {
                $translations = array_merge($translations, $tmpTranslationData);
            }
        }

        // User load
        foreach ($base as $filename => $path) {
            if (!is_file($path)) {
                continue;
            }

            $pathinfo = pathinfo($path);
            $type = $pathinfo['extension'];
            $fileBase = basename($filename, ".{$type}");
            if (!in_array($type, ['csv', 'po'])) {
                continue;
            }

            // User translations (if exists)!
            $userTranslationCSV = $userTranslationsDirectory . $fileBase . '.csv';
            $userTranslationPO = $userTranslationsDirectory . $fileBase . '.po';

            if (is_file($userTranslationPO)) {
                $tmpTranslationData = self::parseTypeForBackoffice($tmpTranslationData, $filename, $userTranslationPO, 'po', 'user');
            } else if (is_file($userTranslationCSV)) {
                $tmpTranslationData = self::parseTypeForBackoffice($tmpTranslationData, $filename, $userTranslationCSV, 'csv', 'user');
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
     */
    public static function parseTypeForBackoffice($tmpTranslationData, $filename, $path, $type, $fillKey = "user")
    {
        // Gettext / CSV selector! MsgID!
        if (!array_key_exists($filename, $tmpTranslationData)) {
            $tmpTranslationData[$filename] = [];
        }

        switch ($type) {
            case 'csv':
                $csvResource = fopen($path, "r");
                while ($line = fgetcsv($csvResource, 1024, ";", '"')) {
                    $key = str_replace('\"', '"', $line[0]);
                    if (!isset($tmpTranslationData[$filename][$key])) {
                        $tmpTranslationData[$filename][$key] = [
                            'flags' => null,
                            'comments' => null,
                            'context' => null,
                            'original' => $key,
                            'default' => null,
                            'user' => null,
                        ];
                    }
                    if (isset($line[1]) && !empty($line[1])) {
                        $tmpTranslationData[$filename][$key][$fillKey] = str_replace('\"', '"', $line[1]);
                    }

                }
                fclose($csvResource);

                break;
            case 'po':
                $userTranslations = new Translations();
                $userTranslations->addFromPoFile($path);
                foreach ($userTranslations as $userTranslation) {
                    /**
                     * @var $userTranslation Translation
                     */
                    $key = str_replace('\"', '"', $userTranslation->getOriginal());
                    if (!isset($tmpTranslationData[$filename][$key])) {
                        $tmpTranslationData[$filename][$key] = [
                            'flags' => $userTranslation->getFlags(),
                            'comments' => $userTranslation->getComments(),
                            'context' => $userTranslation->getContext(),
                            'original' => $key,
                            'default' => null,
                            'user' => null,
                        ];
                    } else {
                        $tmpTranslationData[$filename][$key]['comments'] = array_merge($tmpTranslationData[$filename][$key]['comments'], $userTranslation->getComments());
                        $tmpTranslationData[$filename][$key]['flags'] = array_merge($tmpTranslationData[$filename][$key]['flags'], $userTranslation->getFlags());
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
