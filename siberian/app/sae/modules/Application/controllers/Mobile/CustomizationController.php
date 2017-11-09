<?php

class Application_Mobile_CustomizationController extends Application_Controller_Mobile_Default {

    public function colorsAction() {
        $this->loadPartials(null, false);
        $html = array('html' => $this->getLayout()->render());
        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

}
