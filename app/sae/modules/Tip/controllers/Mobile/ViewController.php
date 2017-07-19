<?php

class Tip_Mobile_ViewController extends Application_Controller_Mobile_Default {

    public function findallAction() {

        $option = $this->getCurrentOptionValue();
        $currency = Core_Model_Language::getCurrentCurrency();
        $payload = array(
            "currency_symbol" => Core_Model_Language::getCurrentCurrency()->getSymbol(),
            "page_title" => $option->getTabbarName(),
            "format" => $currency->toCurrency(1,array("locale" => $currency->getLocale()))
        );

        $this->_sendJson($payload);
    }
}
