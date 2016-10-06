<?php

class Application_Mobile_TranslationController extends Application_Controller_Mobile_Default {

    public function findallAction() {

        Siberian_Cache_Translation::init();

        $data = array();

        if(Core_Model_Language::getCurrentLanguage() != Core_Model_Language::DEFAULT_LANGUAGE) {
            $data = Core_Model_Translator::getTranslationsFor($this->getApplication()->getDesignCode());
        }

        $this->_sendHtml($data);
    }

}
