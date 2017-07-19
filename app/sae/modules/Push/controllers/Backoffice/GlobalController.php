<?php

class Push_Backoffice_GlobalController extends Backoffice_Controller_Default
{

    public function findallAction() {

        try {

            $data = array(
                "title" => __("Global push notifications"),
                "icon" => "fa-globe",
            );

        } catch(Exception $e) {

            $data = array(
                "error" => true,
                "message" => __("An unknown error occurred while loading the page."),
            );

        }

        $this->_sendJson($data);
    }

    public function sendAction() {

        try {

            if($params = Siberian_Json::decode($this->getRequest()->getRawBody())) {

                # Filter checked applications
                $params["checked"] = array_keys(array_filter($params["checked"], function($v) {
                    return ($v == true);
                }));

                $params["base_url"] = $this->getRequest()->getBaseUrl();

                if(empty($params["title"]) || empty($params["message"])) {
                    throw new Siberian_Exception(__("Title & Message are both required."));
                }

                if(empty($params["checked"]) && !$params["send_to_all"]) {
                    throw new Siberian_Exception(__("Please select at least one application."));
                }

                $push_global = new Push_Model_Message_Global();
                $result = $push_global->createInstance($params, true);

                $data = array(
                    "success" => true,
                    "message" => ($result) ? __("Push message is sent.") : __("No message sent, there is no available applications."),
                );

            } else {
                throw new Siberian_Exception(__("%s, No params sent.", "Push_Backoffice_Global::sendAction"));
            }

        } catch(Exception $e) {

            $message = $e->getMessage();
            $message = (empty($message)) ? __("An unknown error occurred while creating the push notification.") : $message;

            $data = array(
                "error" => true,
                "message" => $message,
            );

        }

        $this->_sendJson($data);

    }


}
