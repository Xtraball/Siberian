<?php

class Sourcecode_Mobile_ViewController extends Application_Controller_Mobile_Default {

    public function _toJson($sourcecode){
        $json = array(
            "id" => $sourcecode->getId(),
            "htmlFilePath" => $this->getRequest()->getBaseUrl().$sourcecode->getHtmlFilePath(),
            "htmlFileCode" => $sourcecode->getHtmlFileCode()
        );
        return $json;
    }

    public function findAction() {

        if($value_id = $this->getRequest()->getParam('value_id')) {

            try {

                $option_value = $this->getCurrentOptionValue();
                $sourcecode = $option_value->getObject();

                $data = array(
                    "sourcecode" => $this->_toJson($sourcecode),
                    "page_title" => $option_value->getTabbarName()
                );
            }
            catch(Exception $e) {
                $data = array('error' => 1, 'message' => $e->getMessage());
            }

        }else{
            $data = array('error' => 1, 'message' => 'An error occurred during process. Please try again later.');
        }

        $this->_sendHtml($data);

    }

}