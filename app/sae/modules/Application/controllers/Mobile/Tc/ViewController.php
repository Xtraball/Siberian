<?php

class Application_Mobile_Tc_ViewController extends Application_Controller_Mobile_Default {

    public function findAction() {

        if($tc_id = $this->getRequest()->getParam("tc_id")) {

            try {

                $tc = new Application_Model_Tc();
                $tc->find($tc_id);
                $data = array(
                    "html_file_path" => $this->getRequest()->getBaseUrl().$tc->getHtmlFilePath(),
                    "page_title" => __("Terms & Conditions")
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
