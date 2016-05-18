<?php

class Radio_Mobile_RadioController extends Application_Controller_Mobile_Default {

    public function _toJson($radio){
        $json = array(
            "url" => $url = addslashes($radio->getLink()),
            'title' => $radio->getTitle()
        );

        return $json;
    }

    public function findAction() {

        if($value_id = $this->getRequest()->getParam('value_id')) {
            
            try {
                
                $radio_repository = new Radio_Model_Radio();
                $radio = $radio_repository->find(array('value_id' => $value_id));

                if(substr($radio->getLink(), -1) == "/") {
                    $stream_tag = ";";
//                    $stream_tag = ";stream.nsv&type=mp3";
                } else {
                    if(mb_stripos($radio->getLink(), "/", 8) > 0) {
                        $stream_tag = "";
//                        $stream_tag = "?stream.nsv&type=mp3";
                    } else {
//                        $stream_tag = "/;stream.nsv&type=mp3";
                        $stream_tag = "/;";
                    }
                }

                $radio->setLink($radio->getLink().$stream_tag);

                $data = array("radio" => $this->_toJson($radio));
            }
            catch(Exception $e) {
                $data = array('error' => 1, 'message' => $e->getMessage());
            }

        } else {
            $data = array('error' => 1, 'message' => $this->_("An error occurred while loading. Please try again later."));
        }

        $this->_sendHtml($data);

    }

}