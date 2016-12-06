<?php

class Translation_Backoffice_ListController extends Backoffice_Controller_Default
{

    public function loadAction() {
        
        $html = array(
            "title" => $this->_("Translations"),
            "icon" => "fa-language",
        );

        $this->_sendHtml($html);

    }

    public function findallAction() {

        $languages = Core_Model_Language::getLanguages();
        $data = array();

        foreach($languages as $lang) {

            $data[] = array(
                "id" => base64_encode($lang->getCode()),
                "code" => $lang->getCode(),
                "name" => $lang->getName()
            );
        }

        $this->_sendHtml($data);
    }

}
