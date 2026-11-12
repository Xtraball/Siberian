<?php

class Cms_Mobile_PrivacypolicyController extends Application_Controller_Mobile_Default {


    public function findAction() {

        if($value_id = \Siberian\Input::int($this->getRequest()->getParam('value_id'), 0, 0)) {

            try {
                $option = $this->getCurrentOptionValue();

                $data = [
                    "page_title" => $option->getTabbarName(),
                    "privacy_policy" => str_replace("#APP_NAME", $this->getApplication()->getName(), $this->getApplication()->getPrivacyPolicy())
                ];

            } catch(Exception $e) {
                $data = [
                    "error" => 1,
                    "message" => $e->getMessage()
                ];
            }

            $this->_sendHtml($data);
        }
    }
}