<?php

/**
 * Class Radio_Mobile_RadioController
 */
class Radio_Mobile_RadioController extends Application_Controller_Mobile_Default {

    /**
     * @param $radio
     * @return array
     */
    public function _toJson($radio){
        $json = [
            "url" => addslashes($radio->getData("link")),
            "title" => $radio->getTitle(),
            "background" => $this->getRequest()->getBaseUrl() .
                "/images/application" . $radio->getBackground(),
        ];

        return $json;
    }

    /**
     * Find action
     */
    public function findAction() {
        if ($valueId = $this->getRequest()->getParam("value_id")) {
            try {
                $radio = (new Radio_Model_Radio())
                    ->find([
                        "value_id" => $valueId
                    ]);

                // test stream only for old versions!
                if ($radio->getVersion() < 2) {
                    // Fix for shoutcast, force stream!
                    $contentType = Siberian_Request::testStream($radio->getData("link"));
                    if(!in_array(explode("/", $contentType)[0], ["audio"]) &&
                        !in_array($contentType, ["application/ogg"])) {
                        if(strrpos($radio->getData("link"), ";") === false) {
                            $radio->setData("link", $radio->getData("link") . "/;");
                        }
                    }
                }

                $data = [
                    "radio" => $this->_toJson($radio)
                ];
            } catch (Exception $e) {
                $data = [
                    "error" => true,
                    "message" => $e->getMessage()
                ];
            }
        } else {
            $data = [
                "error" => true,
                "message" => __("An error occurred while loading. Please try again later.")
            ];
        }

        $this->_sendJson($data);
    }

}