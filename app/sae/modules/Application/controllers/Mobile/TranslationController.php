<?php

/**
 * Class Application_Mobile_TranslationController
 *
 * @cache mobile_translation
 */

class Application_Mobile_TranslationController extends Application_Controller_Mobile_Default {

    public function findallAction() {

        $application = $this->getApplication();
        $app_id = $application->getId();
        $current_language = Core_Model_Language::getCurrentLanguage();

        $cache_id_translation = "pre4812_application_mobile_translation_findall_app_{$app_id}_locale_{$current_language}";

        if(!$result = $this->cache->load($cache_id_translation)) {

            Siberian_Cache_Translation::init();

            $data = array();

            if (Core_Model_Language::getCurrentLanguage() != Core_Model_Language::DEFAULT_LANGUAGE) {
                $data = Core_Model_Translator::getTranslationsFor($application->getDesignCode());
            }

            $this->cache->save($data, $cache_id_translation, array(
                "mobile_translation",
                "mobile_translation_locale_{$current_language}"
            ));

            $data["x-cache"] = "MISS";
        } else {

            $data = $result;

            $data["x-cache"] = "HIT";
        }

        $this->_sendJson($data);
    }

    /**
     * @deprecated
     */
    public function localeAction() {
        die(strtolower(str_replace("_", "-", Core_Model_Language::getCurrentLocale())));
    }

}
