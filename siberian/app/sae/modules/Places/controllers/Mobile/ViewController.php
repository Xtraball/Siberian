<?php

class Places_Mobile_ViewController extends Application_Controller_Mobile_Default {

    public function findAction() {

        if($value_id = $this->getRequest()->getParam('value_id')
           && $place_id = $this->getRequest()->getParam('place_id')) {

            try {

                $pageRepository = new Cms_Model_Application_Page();
                $page = $pageRepository->find($place_id);

                $json = array();

                $blocks = $page->getBlocks();
                $data = array("blocks" => array());

                foreach($blocks as $block) {

                    if($block->getType() == "address") {
                        $json = $this->_toJson($page, $block);
                        // only one address per place
                        break;
                    }

                }
                $data = array("place" => $json);
            }
            catch(Exception $e) {
                $data = array('error' => 1, 'message' => $e->getMessage());
            }

        } else {
            $data = array('error' => 1, 'message' => 'An error occurred during process. Please try again later.');
        }

        $this->_sendJson($data);

    }

    public function _toJson($page, $address) {

        $json = array(
            "id"=> $page->getId(),
            "title"=> $page->getTitle(),
            "content"=> $page->getContent(),
            "picture"=> $page->getPictureUrl(),
            "url" => $this->getUrl("places/mobile_details/index", array("value_id" => $page->getValueId(), "place_id" => $page->getId())),
            "address" => array(
                "id" => $address->getId(),
                "position" => $address->getPosition(),
                "block_id" => $address->getBlockId(),
                "label" => $address->getLabel(),
                "address" => $address->getAddress(),
                "latitude" => $address->getLatitude(),
                "longitude" => $address->getLongitude(),
                "show_address" => !!($address->getShowAddress()),
                "show_geolocation_button" => !!($address->getShowGeolocationButton())
            )
        );

        return $json;

    }


}