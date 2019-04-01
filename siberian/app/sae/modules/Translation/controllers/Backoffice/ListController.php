<?php

/**
 * Class Translation_Backoffice_ListController
 */
class Translation_Backoffice_ListController extends Backoffice_Controller_Default {
    /**
     *
     */
    public function loadAction() {
        $payload = [
            "title" => sprintf("%s > %s",
                __("Settings"),
                __("Translations")),
            "icon" => "fa-language",
        ];

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function findallAction() {
        $languages = Core_Model_Language::getLanguages();
        $payload = [];

        foreach($languages as $lang) {
            $payload[] = [
                "id" => base64_encode($lang->getCode()),
                "code" => $lang->getCode(),
                "name" => $lang->getName()
            ];
        }

        $this->_sendJson($payload);
    }
}
