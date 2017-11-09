<?php

class Social_Mobile_Facebook_ListController extends Application_Controller_Mobile_Default {

    public function findAction() {
        if ($value_id = $this->getRequest()->getParam("value_id")) {

            $facebook = $this->getCurrentOptionValue()->getObject();
            $data = array(
                "username" => $facebook->getFbUser(),
                "token" => $facebook->getAccessToken(),
                "page_title" => $this->getCurrentOptionValue()->getTabbarName()
            );
            $this->_sendJson($data);
        }

    }
}