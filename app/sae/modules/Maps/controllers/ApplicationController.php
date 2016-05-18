<?php

class Maps_ApplicationController extends Application_Controller_Default {

    public function editpostAction() {

        if($data = $this->getRequest()->getParams()) {

            try {

                $maps = new Maps_Model_Maps();
                $maps->find($data["value_id"],"value_id");

                if(!$data["address"]) {
                    throw new Exception($this->_("Address is mandatory."));
                }

                $data["latitude"] = $data["cms_latitude_0"];
                $data["longitude"] = $data["cms_longitude_0"];

                $maps->setData($data)
                        ->save();

                $html = array(
                    'success' => '1',
                    'success_message' => $this->_('Info successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );

            } catch (Exception $e) {
                $html = array(
                    "message" => $e->getMessage(),
                    "error" => 1
                );
            }
        } else {
            $html = array(
                "message" => $this->_("An error occurred during the process. Please try again later."),
                "error" => 1
            );
        }

        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

}