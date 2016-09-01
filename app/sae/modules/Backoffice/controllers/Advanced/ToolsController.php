<?php

class Backoffice_Advanced_ToolsController extends System_Controller_Backoffice_Default {

    public function loadAction() {

        $html = array(
            "title" => __("Advanced")." > ".__("Tools"),
            "icon" => "fa-file-code-o",
        );

        $this->_sendHtml($html);

    }

    public function runtestAction() {

        $data = Siberian_Tools_Integrity::checkIntegrity();

        $this->_sendHtml($data);

    }

    public function saveAction() {

        if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {
                $this->_save($data);

                $data = array(
                    "success" => 1,
                    "message" => __("Configuration successfully saved")
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
