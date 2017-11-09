<?php

class Weblink_Mobile_MultiController extends Application_Controller_Mobile_Default {

    public function indexAction() {
        $this->forward('index', 'index', 'Front', $this->getRequest()->getParams());
    }

    public function templateAction() {
        $this->loadPartials($this->getFullActionName('_').'_l'.$this->_layout_id, false);
    }

    public function findAction() {

        $option = $this->getCurrentOptionValue();
        $payload = $option->getObject()->getEmbedPayload($option);
        $this->_sendJson($payload);

    }

}