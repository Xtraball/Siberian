<?php

/**
 * Class Core_Model_Translator
 */
class Core_Model_Translator
{
    /**
     * @var
     */
    public static $_translator;

    /**
     * @param null $platform
     * @return array
     */
    public static function getTranslationsFor($platform = null)
    {
        return self::_getIonicTranslations();
    }

    /**
     * @param $moduleName
     * @throws Zend_Translate_Exception
     * @throws Zend_Validate_Exception
     */
    public static function prepare($moduleName)
    {
        $current_language = Core_Model_Language::getCurrentLanguage();

        if (!file_exists(Core_Model_Directory::getBasePathTo("/languages/$current_language/default.csv"))) {
            return;
        }

        self::$_translator = new Zend_Translate([
            'adapter' => 'csv',
            'content' => Core_Model_Directory::getBasePathTo("/languages/$current_language/default.csv"),
            'locale' => $current_language
        ]);

        if (file_exists(Core_Model_Directory::getBasePathTo("/languages/{$current_language}/default-group1.csv"))) {
            self::$_translator->addTranslation([
                'content' => Core_Model_Directory::getBasePathTo("/languages/{$current_language}/default-group1.csv"),
                'locale' => $current_language
            ]);
        }

        if (file_exists(Core_Model_Directory::getBasePathTo("/languages/{$current_language}/default-group2.csv"))) {
            self::$_translator->addTranslation([
                'content' => Core_Model_Directory::getBasePathTo("/languages/{$current_language}/default-group2.csv"),
                'locale' => $current_language
            ]);
        }

        if (file_exists(Core_Model_Directory::getBasePathTo("/languages/{$current_language}/emails/default.csv"))) {
            self::$_translator->addTranslation([
                'content' => Core_Model_Directory::getBasePathTo("/languages/{$current_language}/emails/default.csv"),
                'locale' => $current_language
            ]);
        }

        $form_translator = new Zend_Translate([
            'adapter' => 'array',
            'content' => Core_Model_Directory::getBasePathTo("lib/Zend/resources/languages"),
            'locale' => $current_language,
            'scan' => Zend_Translate::LOCALE_DIRECTORY
        ]);

        Zend_Validate_Abstract::setDefaultTranslator($form_translator);

        try {
            $frontController = Zend_Controller_Front::getInstance();
            $moduleNames = $frontController->getDispatcher()->getModuleDirectories();

            // Load all defaults first!
            foreach ($moduleNames as $moduleName) {
                self::addModuleDefaults($frontController->getModuleDirectory($moduleName));
            }

            // Then load all user translations!
            foreach ($moduleNames as $moduleName) {
                $dashName = strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $moduleName));
                $lowerName = strtolower($moduleName);

                self::addModule($dashName);
                self::addModule($lowerName);
            }
        } catch (\Exception $e) {
            if ($moduleName != 'application') {
                self::addModule('application');
            }

            if ($moduleName != 'whitelabel') {
                self::addModule('whitelabel');
            }

            self::addModule($moduleName);
        }
        return;

    }

    /**
     * @param $modulePath
     */
    public static function addModuleDefaults($modulePath)
    {
        $currentLanguage = Core_Model_Language::getCurrentLanguage();
        $folder = "{$modulePath}/resources/translations/{$currentLanguage}";
        if (is_dir($folder)) {
            // Get all translations folders
            $files = new DirectoryIterator($folder);
            foreach ($files as $file) {
                if (!$file->isDot()) {
                    self::$_translator->addTranslation([
                        'adapter' => ($file->getExtension() === "csv") ? "csv" : "gettext",
                        'content' => $file->getPathname(),
                        'locale' => $currentLanguage
                    ]);
                }
            }
        }
    }

    /**
     * @param $moduleName
     */
    public static function addModule($moduleName)
    {
        $currentLanguage = Core_Model_Language::getCurrentLanguage();
        if (file_exists(Core_Model_Directory::getBasePathTo("/languages/{$currentLanguage}/{$moduleName}.csv"))) {
            self::$_translator->addTranslation([
                'adapter' => 'csv',
                'content' => Core_Model_Directory::getBasePathTo("/languages/{$currentLanguage}/{$moduleName}.csv"),
                'locale' => $currentLanguage
            ]);
        }

        if (file_exists(Core_Model_Directory::getBasePathTo("/languages/{$currentLanguage}/{$moduleName}.mo"))) {
            self::$_translator->addTranslation([
                'adapter' => 'gettext',
                'content' => Core_Model_Directory::getBasePathTo("/languages/{$currentLanguage}/{$moduleName}.mo"),
                'locale' => $currentLanguage
            ]);
        }

        if (file_exists(Core_Model_Directory::getBasePathTo("/languages/{$currentLanguage}/emails/{$moduleName}.csv"))) {
            self::$_translator->addTranslation([
                'adapter' => 'csv',
                'content' => Core_Model_Directory::getBasePathTo("/languages/{$currentLanguage}/emails/{$moduleName}.csv"),
                'locale' => $currentLanguage
            ]);
        }
    }

    /**
     * @param $text
     * @param array $args
     * @return mixed|string
     */
    public static function translate($text, array $args = [])
    {

        $translator = self::$_translator;

        if (count($args) > 1) {
            unset($args[0]);
        }

        $text = stripslashes($text);
        $orig_text = $text = stripslashes($text);

        if (!is_null($translator)) {
            $text = $translator->_(trim($text));
        }

        if (is_array($text)) {
            return $orig_text;
        }

        if (count($args) > 0) {
            while (count($args) < substr_count($text, '%s')) {
                $args[] = '';
            }
            array_unshift($args, $text);
            $text = call_user_func_array('sprintf', $args);
        }

        return $text;
    }

    /**
     * @return array
     */
    protected static function _getAngularTranslations()
    {

        $modules = ["mcommerce", "comment"];
        $translations = [];

        foreach ($modules as $module) {
            self::addModule($module);
        }

        $texts_to_translate = [
            "OK",
            "Website",
            "Phone",
            "Locate",
            "Contact successfully added to your address book",
            "Unable to add the contact to your address book",
            "You must give the permission to the app to add a contact to your address book",
            "You already have this user in your contact",
            "The address you're looking for does not exists.",
            "An error occurred while loading. Please, try again later.",
            // Map
            "An unexpected error occurred while calculating the route.",
            // Mcommerce
            "Cart",
            "Proceed",
            "Next",
            "Payment",
            "Delivery",
            "My information",
            "Review",
            "Some mandatory fields are empty.",
            "Validate",
            "The payment has been cancelled, something wrong happened? Feel free to contact us.",
            // Places
            "Map",
            "Invalid place",
            "Unable to calculate the route.",
            "No address to display on map.",
            "You must share your location to access this page.",
            // Comment
            "No place to display on map.",
            "An error occurred while loading places.",

            // General
            "Load More",
            "This section is unlocked for mobile users only",
            "You have gone offline",
            "Cancel",
            "Confirm",
            "View",
            "Offline content",
            "Don't close the app while downloading. This may take a while.",
            "Do you want to download all the contents now to access it when offline? If you do, we recommend you to use a WiFi connection."
        ];

        foreach ($texts_to_translate as $text_to_translate) {
            $translations[$text_to_translate] = self::translate($text_to_translate);
        }

        return $translations;

    }

    /**
     * @todo To cache
     *
     * @return array
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

        $allTranslations = [];
        $allFilesTranslations = self::parseTranslations(Core_Model_Language::getCurrentLanguage());

        foreach ($allFilesTranslations as $file => $translations) {
            $allTranslations = array_merge($allTranslations, $translations);
        }

        $translations = array_intersect_key($allTranslations, array_flip($keys));

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

        // Fetching all keys!
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

            // Easy
            $tmpTranslationData = self::parseType([], $filename, $path, $type);

            // Default translation (if exists) mixed csv/mo, mo being more recent!
            $defaultTranslationCSV = $cachedFiles[$langId]["{$fileBase}.csv"];
            if (is_file($defaultTranslationCSV)) {
                $tmpTranslationData = self::parseType($tmpTranslationData, $filename, $defaultTranslationCSV, "csv");
            }
            $defaultTranslationMO = $cachedFiles[$langId]["{$fileBase}.mo"];
            if (is_file($defaultTranslationMO)) {
                $tmpTranslationData = self::parseType($tmpTranslationData, $filename, $defaultTranslationMO, "mo");
            }

            // User translations (if exists)!
            $userTranslationCSV = $userTranslationsDirectory . $fileBase . ".csv";
            $userTranslationMO = $userTranslationsDirectory . $fileBase . ".mo";

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
                $csvResource = fopen($path, "r");
                while ($line = fgetcsv($csvResource, 1024, ";", '"')) {
                    $key = str_replace('\"', '"', $line[0]);
                    $tmpTranslationData[$filename][$key] = null;
                    if (isset($line[1])) {
                        $tmpTranslationData[$filename][$key] = str_replace('\"', '"', $line[1]);
                    }

                }
                fclose($csvResource);

                break;
            case "mo":
                /**
                 * @var $translator Zend_Translate_Adapter_Gettext
                 */
                $translator = new \Zend_Translate([
                    "adapter" => "gettext",
                    "content" => $path,
                    "locale" => "en"
                ]);
                $_tmp = $translator->getData("en");
                foreach ($_tmp as $key => $value) {
                    $key = str_replace('\"', '"', $key);
                    $tmpTranslationData[$filename][$key] = $value;
                }

                break;
        }

        return $tmpTranslationData;
    }
}
