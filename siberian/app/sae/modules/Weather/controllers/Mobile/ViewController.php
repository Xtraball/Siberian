<?php

class Weather_Mobile_ViewController extends Application_Controller_Mobile_Default {

    public function findAction() {

        if($value_id = $this->getRequest()->getParam('value_id')) {

            try {

                $weather = new Weather_Model_Weather();
                $weather->find($value_id, "value_id");

                $data = array(
                    "collection" => array(),
                    "page_title" => $this->getCurrentOptionValue()->getTabbarName(),
                    "icon_url" => $this->_getImage("weather/")
                );

                $data["collection"] = $weather->getData();

            }
            catch(Exception $e) {
                $data = array('error' => 1, 'message' => $e->getMessage());
            }

        } else {
            $data = array('error' => 1, 'message' => 'An error occurred during process. Please try again later.');
        }

        $this->_sendJson($data);

    }

    public function proxyAction() {

        try {

            if($params = Siberian_Json::decode($this->getRequest()->getRawBody())) {

                $_request = base64_decode($params["request"]);

                if(strpos($_request, "yahoo") === false) {
                    throw new Siberian_Exception(__("Not a Yahoo weather request, aborting."));
                }

                $response = Siberian_Request::get($_request);

                $payload = Siberian_Json::decode($response);

            } else {

                throw new Siberian_Exception(__("Missing request."));
            }

        } catch(Exception $e) {
            $payload = array(
                "error" => true,
                "message" => $e->getMessage()
            );
        }

        $this->_sendJson($payload);
    }


}