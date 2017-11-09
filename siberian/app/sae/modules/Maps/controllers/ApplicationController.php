<?php

class Maps_ApplicationController extends Application_Controller_Default {

    /**
     * @var array
     */
    public $cache_triggers = array(
        "editpost" => array(
            "tags" => array(
                "homepage_app_#APP_ID#",
            ),
        ),
    );

    public function editpostAction() {

        if($data = $this->getRequest()->getParams()) {

            try {

                $maps = new Maps_Model_Maps();
                $maps->find($data["value_id"],"value_id");

                if(!$data["address"]) {
                    throw new Siberian_Exception(__("Address is mandatory."));
                }

                $data["latitude"] = $data["cms_latitude_0"];
                $data["longitude"] = $data["cms_longitude_0"];

                $maps->setData($data)
                        ->save();

                $this->getCurrentOptionValue()
                    ->touch()
                    ->expires(-1);

                $payload = array(
                    'success' => true,
                    'success_message' => __('Info successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );

            } catch (Exception $e) {
                $payload = array(
                    "error" => true,
                    "message" => $e->getMessage()

                );
            }

        } else {
            $payload = array(
                "error" => true,
                "message" => __("An error occurred during the process. Please try again later.")

            );
        }

        $this->_sendJson($payload);
    }

}