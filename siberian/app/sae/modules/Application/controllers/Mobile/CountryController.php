<?php

class Application_Mobile_CountryController extends Application_Controller_Mobile_Default {

    public function findallAction() {

        $data = array();

        $countries = Zend_Registry::get('Zend_Locale')->getTranslationList('Territory', null, 2);
        asort($countries, SORT_LOCALE_STRING);

        foreach($countries as $key => $country) {
            $data[] = array(
                "code" => $key,
                "label" => $country
            );
        }
        $this->_sendHtml($data);
    }

}
