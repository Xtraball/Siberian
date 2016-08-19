<?php

class Core_Model_Language {

    const DEFAULT_LOCALE = 'en_US';
    const DEFAULT_LANGUAGE = 'en';

    protected static $_countries_list;
    protected static $_current_currency;
    protected static $__session = null;
    protected static $_languages = array();
    protected static $_language_codes = array();

    public static function prepare() {

        $territories = Zend_Locale::getTranslationList('language');
        $directories = new DirectoryIterator(Core_Model_Directory::getBasePathTo('languages'));
        foreach($directories as $directory) {
            $dir_name = $directory->getFileName();
            if(!$directory->isDot() AND isset($territories[$dir_name])) {
                $locale = Zend_Locale::getLocaleToTerritory($dir_name);
                self::$_languages[$directory->getFileName()] = new Core_Model_Default(array(
                    'code' => $directory->getFileName(),
                    'name' => ucfirst($territories[$dir_name]),
                    'locale' => $locale,
                ));
                self::$_language_codes[] = $directory->getFileName();
            }
        }
        self::$_languages[self::DEFAULT_LANGUAGE] = new Core_Model_Default(array(
            'code' => self::DEFAULT_LANGUAGE,
            'name' => ucfirst($territories[self::DEFAULT_LANGUAGE]),
            'locale' => self::DEFAULT_LOCALE
        ));

        self::$_language_codes[] = self::DEFAULT_LANGUAGE;

        asort(self::$_languages);

    }

    public static function setSession($session) {
        self::$__session = $session;
    }

    public static function getSession() {
        return self::$__session;
    }

    public static function getLanguages() {
        return self::$_languages;
    }

    public static function getLanguage($language_code) {
        return isset(self::$_languages[$language_code]) ? self::$_languages[$language_code] : new Core_Model_Default();
    }

    public static function getLanguageCodes() {
        return self::$_language_codes;
    }

    public static function getDefaultLanguage() {
        return self::DEFAULT_LANGUAGE;
    }

    public static function getDefaultLocale() {
        return self::DEFAULT_LOCALE;
    }

    public static function setCurrentLanguage($territory) {
        if(self::$__session) {
            self::$__session->current_language = $territory;
        }
    }

    public static function getCurrentLanguage() {
        $current_language = self::getDefaultLanguage();
        if(self::$__session) {
            $current_language = self::$__session->current_language;
        }

        return $current_language;
    }

    /**
     * @return mixed
     */
    public static function getCurrentLanguageDatepicker() {
        $parts = explode("_", self::getCurrentLanguage());

        return $parts[0];
    }

    public static function getCurrentLocale() {
        return Zend_Registry::isRegistered("Zend_Locale") ? Zend_Registry::get('Zend_Locale') : self::getDefaultLocale();
        $language = self::getLanguage(self::getCurrentLanguage());
        return $language->getLocale() ? $language->getLocale() : self::getDefaultLocale();
    }

    public static function getCurrentLocaleCode() {
        return Zend_Registry::get('Zend_Locale')->toString();
    }

    public static function setCurrentCurrency($currency, $locale = null) {
        if(!is_null($locale)) $currency->setLocale($locale);
        self::$_current_currency = $currency;
    }

    public static function getCurrentCurrency() {
        if(!self::$_current_currency instanceof Zend_Currency) {
            self::$_current_currency = new Zend_Currency();
        }
        return self::$_current_currency;
    }

    public static function getCurrencySymbol() {
        return self::getCurrentCurrency()->getSymbol();
    }

    public static function normalizePrice($price, $locale = null) {

        foreach(self::getCountriesList() as $country) {
            $price = str_replace($country->getSymbol(), '', $price);
        }

        return $price;
    }

    public static function getCountriesList() {

        if(is_null(self::$_countries_list)) {

            self::$_countries_list = array();
            $currency = self::$_current_currency ? self::$_current_currency : new Zend_Currency();

            foreach(Zend_Locale::getTranslationList('Territory', self::getCurrentLocale(), 2) as $ter => $name) {
                $country_code = Zend_Locale::getLocaleToTerritory($ter);

                if(!is_null($country_code)) {
                    try {
                        $symbol = $currency->getSymbol($country_code);
                        if($ter == "RS") {
                            $country_code = "sr_RS";
                            $symbol = $currency->getSymbol($country_code);
                        }
                        if(!empty($symbol)) {
                            $countries[$country_code] = array(
                                'code' => $country_code,
                                'name' => $name,
                                'symbol' => $symbol
                            );
                        }
                    }
                    catch(Exception $e) {}

                }
            }

            uasort($countries, 'cmp');
            foreach($countries as $currency) {
                self::$_countries_list[] = new Core_Model_Default($currency);
            }

        }

        return self::$_countries_list;
    }

}

function cmp($a, $b) {
    $cmp = strcmp($a['name'], $b['name']);
    if ($cmp == 0) {
        return 0;
    }
    return ($cmp < 0) ? -1 : 1;
}