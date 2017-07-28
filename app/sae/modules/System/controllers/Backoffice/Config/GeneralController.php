<?php

class System_Backoffice_Config_GeneralController extends System_Controller_Backoffice_Default {


    /**
     * @var array
     */
    public $cache_triggers = array(
        "save" => array(
            "tags" => array(
                "front_mobile_load",
            ),
        )
    );

    protected $_codes = array(
        "platform_name",
        "company_name",
        "company_phone",
        "company_address",
        "company_country",
        "company_vat_number",
        "system_timezone",
        "system_currency",
        "system_default_language",
        "system_publication_access_type",
        "system_generate_apk",
        "application_ios_owner_admob_id",
        "application_ios_owner_admob_interstitial_id",
        "application_ios_owner_admob_type",
        "application_ios_owner_admob_weight",
        "application_android_owner_admob_id",
        "application_android_owner_admob_interstitial_id",
        "application_android_owner_admob_type",
        "application_android_owner_admob_weight",
        "application_owner_use_ads",
        "editor_design",
        "ios_autobuild_key",
        "bootstraptour_active",
        "facebook_import_active",
        "app_default_identifier_android",
        "app_default_identifier_ios"
    );

    public function loadAction() {

        $html = array(
            "title" => __("General"),
            "icon" => "fa-home",
        );

        $this->_sendHtml($html);

    }

    public function findallAction() {

        $data = $this->_findconfig();
        
        $timezones = DateTimeZone::listIdentifiers();
        if(empty($timezones)) {
            $locale = Zend_Registry::get("Zend_Locale");
            $timezones = $locale->getTranslationList('TimezoneToTerritory');
        }
        foreach($timezones as $timezone) {
            $data["territories"][$timezone] = $timezone;
        }

        foreach(Core_Model_Language::getCountriesList() as $country) {
            $data["currencies"][$country->getCode()] = $country->getName() . " ({$country->getSymbol()})";
        }

        $countries = $countries = Zend_Registry::get('Zend_Locale')->getTranslationList('Territory', null, 2);;
        asort($countries, SORT_LOCALE_STRING);
        $data["countries"] = $countries;

        $languages = array();
        foreach(Core_Model_Language::getLanguages() as $language) {
            $languages[$language->getCode()] = $language->getName();
        }
        if(!empty($languages) AND count($languages) > 1) {
            $data["languages"] = $languages;
        }

        $data["application_android_owner_admob_weight"]["value"] = (integer) $data["application_android_owner_admob_weight"]["value"];
        $data["application_ios_owner_admob_weight"]["value"] = (integer) $data["application_ios_owner_admob_weight"]["value"];

        $this->_sendHtml($data);

    }

    public function saveAction() {

        if($data = Siberian_Json::decode($this->getRequest()->getRawBody())) {

            try {

                if(!empty($data["application_free_trial"]["value"]) AND !is_numeric($data["application_free_trial"]["value"])) {
                    throw new Exception("Free trial period duration must be a numeric value.");
                }

                $this->_save($data);

                $data = array(
                    "success" => 1,
                    "message" => __("Info successfully saved")
                );
            } catch(Exception $e) {
                $data = array(
                    "error" => 1,
                    "message" => $e->getMessage()
                );
            }

            $this->_sendHtml($data);

        }

    }

}
