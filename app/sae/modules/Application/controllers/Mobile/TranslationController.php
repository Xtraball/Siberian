<?php

/**
 * Class Application_Mobile_TranslationController
 *
 * @cache mobile_translation
 */

class Application_Mobile_TranslationController extends Application_Controller_Mobile_Default {

    public function findallAction() {

        $application = $this->getApplication();

        $cache_id = "application_mobile_translation_findall_app_{$application->getId()}";

        if(!$result = $this->cache->load($cache_id)) {

            Siberian_Cache_Translation::init();

            $data = array();

            if (Core_Model_Language::getCurrentLanguage() != Core_Model_Language::DEFAULT_LANGUAGE) {
                $data = Core_Model_Translator::getTranslationsFor($application->getDesignCode());
            }

            $this->cache->save($data, $cache_id, array("mobile_translation"));

            $data["x-cache"] = "MISS";
        } else {

            $data = $result;

            $data["x-cache"] = "HIT";
        }

        $this->_sendJson($data);
    }

    public function localeAction() {
        die(strtolower(str_replace("_", "-", Core_Model_Language::getCurrentLocale())));
    }

}
