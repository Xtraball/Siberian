<?php

class Social_Mobile_Facebook_ListController extends Application_Controller_Mobile_Default {

    public function findAction() {
        if ($value_id = \Siberian\Input::int($this->getRequest()->getParam("value_id"), 0, 0)) {

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