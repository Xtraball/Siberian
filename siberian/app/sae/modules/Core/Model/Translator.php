<?php

class Core_Model_Translator
{

    public static $_translator;

    public static function getTranslationsFor($platform) {

        $translations = array();

        if($platform == Application_Model_Application::DESIGN_CODE_ANGULAR) {
            $translations = self::_getAngularTranslations();
        } else if($platform == Application_Model_Application::DESIGN_CODE_IONIC) {
            $translations = self::_getIonicTranslations();
        }

        return $translations;

    }

    public static function prepare($module_name) {

        $current_language = Core_Model_Language::getCurrentLanguage();

        if(!file_exists(Core_Model_Directory::getBasePathTo("/languages/$current_language/default.csv"))) return;

        self::$_translator = new Zend_Translate(array(
            'adapter' => 'csv',
            'content' => Core_Model_Directory::getBasePathTo("/languages/$current_language/default.csv"),
            'locale' => $current_language
        ));

        if(file_exists(Core_Model_Directory::getBasePathTo("/languages/{$current_language}/emails/default.csv"))) {
            self::$_translator->addTranslation(array(
                'content' => Core_Model_Directory::getBasePathTo("/languages/{$current_language}/emails/default.csv"),
                'locale' => $current_language
            ));
        }

        $form_translator = new Zend_Translate(array(
            'adapter' => 'array',
            'content' => Core_Model_Directory::getBasePathTo("lib/Zend/resources/languages"),
            'locale'  => $current_language,
            'scan' => Zend_Translate::LOCALE_DIRECTORY
        ));

        Zend_Validate_Abstract::setDefaultTranslator($form_translator);

        if($module_name != 'application') {
            self::addModule('application');
        }
        if($module_name != 'whitelabel') {
            self::addModule('whitelabel');
        }

        self::addModule($module_name);

        return;

    }

    public static function addModule($module_name) {

        $current_language = Core_Model_Language::getCurrentLanguage();
        if(file_exists(Core_Model_Directory::getBasePathTo("/languages/{$current_language}/{$module_name}.csv"))) {
            self::$_translator->addTranslation(array(
                'content' => Core_Model_Directory::getBasePathTo("/languages/$current_language/{$module_name}.csv"),
                'locale' => $current_language
            ));
        }
        if(file_exists(Core_Model_Directory::getBasePathTo("/languages/{$current_language}/emails/{$module_name}.csv"))) {
            self::$_translator->addTranslation(array(
                'content' => Core_Model_Directory::getBasePathTo("/languages/{$current_language}/emails/{$module_name}.csv"),
                'locale' => $current_language
            ));
        }

    }

    public static function translate($text, array $args = array()) {

        $translator = self::$_translator;
        
        if(count($args) > 1) {
            unset($args[0]);
        }

        $text = stripslashes($text);
        $orig_text = $text = stripslashes($text);

        if(!is_null($translator)) {
            $text = $translator->_(trim($text));
        }

        if(is_array($text)) {
            return $orig_text;
        }

        if(count($args) > 0) {
            while(count($args) < substr_count($text, '%s')) {
                $args[] = '';
            }
            array_unshift($args, $text);
            $text = call_user_func_array('sprintf', $args);
        }

        return $text;
    }

    protected static function _getAngularTranslations() {

        $modules = array("mcommerce", "comment");
        $translations = array();

        foreach($modules as $module) {
            self::addModule($module);
        }

        $texts_to_translate = array(
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
        );

        foreach($texts_to_translate as $text_to_translate) {
            $translations[$text_to_translate] = self::translate($text_to_translate);
        }

        return $translations;

    }

    /**
     * @todo To cache
     *
     * @return array
     */
    protected static function _getIonicTranslations() {

        $translation_cache = Siberian_Cache_Translation::getCache();
        $mobile_files = $translation_cache["mobile_list"];

        $keys = array();
        $translations = array();

        foreach($mobile_files as $mobile_file) {
            $resource = fopen($mobile_file, "r");
            while($data = fgetcsv($resource, 1024, ";", "\"")) {
                if(!empty($data[0]) AND stripos($data[0], "%s") === false) {
                    $keys[] = $data[0];
                }
            }
        }

        $flipped_keys = array_flip($keys);

        $current_language = Core_Model_Language::getCurrentLanguage();
        $translation_files_path = Core_Model_Directory::getBasePathTo("languages/{$current_language}");
        if(is_dir($translation_files_path)) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($translation_files_path), RecursiveIteratorIterator::SELF_FIRST);

            foreach ($files as $file) {
                if (!$file->isFile()) continue;
                if (pathinfo($file->getPathName(), PATHINFO_EXTENSION) != "csv") continue;

                $resource = fopen($file->getPathName(), "r");
                while ($data = fgetcsv($resource, 1024, ";", "\"")) {
                    if (!empty($data[0]) AND !empty($data[1]) AND isset($flipped_keys[$data[0]]) ) {
                        $translations[$data[0]] = $data[1];
                    }
                }

            }
        }

        return $translations;

    }

}