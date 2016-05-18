<?php

class Weather_ApplicationController extends Application_Controller_Default {

    public function editpostAction() {

        if($data = $this->getRequest()->getParams()) {

            try {

                $weather = new Weather_Model_Weather();
                $weather->find($data["value_id"],"value_id");

                if(!$data["country_code"]) {
                    $data["city"]  = null;
                    $data["woeid"] = null;
                } else {
                    if($data["city"]) {
                        $text_param = $data["city"].",".$data["country_code"];
                    } else {
                        $text_param = $data["country_code"];
                    }

                    $uri = str_replace(" ", "%20", "select woeid from geo.places where text='".$text_param."'&format=json");
                    $query = "http://query.yahooapis.com/v1/public/yql?q=$uri";
                    $contents = file_get_contents($query);

                    if(!empty($contents)) {
                        $json_data = Zend_Json::decode($contents);
                        if($json_data["query"]["count"]> 0) {
                            if(count($json_data["query"]["results"]["place"])>1) {
                                $woeid = $json_data["query"]["results"]["place"][0]["woeid"];
                            } else {
                                $woeid = $json_data["query"]["results"]["place"]["woeid"];
                            }
                        } else {
                            $woeid = null;
                        }
                    } else {
                        $woeid = null;
                    }

                    $data["woeid"] = $woeid;
                }

                $weather->setData($data)
                    ->save()
                ;

                $html = array(
                    'success' => '1',
                    'success_message' => $this->_('Info successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );

            } catch (Exception $e) {
                $html = array(
                    'message' => $e->getMessage()
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