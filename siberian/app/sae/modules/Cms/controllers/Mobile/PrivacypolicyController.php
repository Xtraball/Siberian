<?php

class Cms_Mobile_PrivacypolicyController extends Application_Controller_Mobile_Default {


    public function findAction() {

        if($value_id = $this->getRequest()->getParam('value_id')) {

            try {
                $option = $this->getCurrentOptionValue();

                $data = array(
                    "page_title" => $option->getTabbarName(),
                    "privacy_policy" => str_replace("#APP_NAME", $this->getApplication()->getName(), $this->getApplication()->getPrivacyPolicy())
                );

            } catch(Exception $e) {
                $data = array(
                    "error" => 1,
                    "message" => $e->getMessage()
                );
            }

            $this->_sendHtml($data);
        }
    }
}