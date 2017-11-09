<?php

class Radio_MobileController extends Application_Controller_Mobile_Default {

    public function viewAction() {
        $option = $this->getCurrentOptionValue();
        $radio = $option->getObject();
        $url = addslashes($radio->getLink());
        $html = array('url' => $url, 'title' => $radio->getTitle(), 'callback' => 'page.launchRadio', 'page_id' => $option->getId());
        $this->getResponse()->setBody(Zend_Json::encode($html))->sendResponse();
        die;
    }

}