<?php

class Front_Mobile_GmapsController extends Application_Controller_Mobile_Default {

    public function viewAction() {

        $this->loadPartials(null, false);
        $html = array('html' => $this->getLayout()->render(), 'title' => $this->_('Maps'));
        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

}