<?php

class Places_Mobile_ViewController extends Application_Controller_Mobile_Default {

    public function findAction() {

        if($value_id = $this->getRequest()->getParam('value_id')
           && $place_id = $this->getRequest()->getParam('place_id')) {

            try {

                $pageRepository = new Cms_Model_Application_Page();
                $page = $pageRepository->find($place_id);

                $json = [];

                $blocks = $page->getBlocks();
                $data = ["blocks" => []];

                foreach($blocks as $block) {

                    if($block->getType() == "address") {
                        $json = $this->_toJson($page, $block);
                        // only one address per place
                        break;
                    }

                }
                $data = ["place" => $json];
            }
            catch(Exception $e) {
                $data = ['error' => 1, 'message' => $e->getMessage()];
            }

        } else {
            $data = ['error' => 1, 'message' => 'An error occurred during process. Please try again later.'];
        }

        $this->_sendJson($data);

    }

    /**
     * @throws Zend_Controller_Response_Exception
     * @throws Zend_Exception
     */
    public function saveNoteAction() {
        try {
            $request = $this->getRequest();
            $session = $this->getSession();
            $customerId = $session->getCustomerId();
            $params = $request->getBodyParams();
            $option = $this->getCurrentOptionValue();

            $note = new Places_Model_CustomerNote();

            $note
                ->setValueId($option->getId())
                ->setCustomerId($customerId)
                ->setPlaceId($params["place_id"])
                ->setNote($params["note"])
                ->save();

            $payload = [
                "success" => true,
                "message" => p__("places", "Note is saved!"),
                "note" => $note->getData()
            ];
        } catch (\Exception $e) {
            $payload = [
                "eror" => true,
                "message" => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    public function deleteNoteAction() {
        try {
            $request = $this->getRequest();
            $session = $this->getSession();
            $customerId = $session->getCustomerId();
            $params = $request->getBodyParams();
            $option = $this->getCurrentOptionValue();

            $note = (new Places_Model_CustomerNote())->find([
                "customer_note_id" => $params["note_id"],
                "value_id" => $option->getId(),
                "place_id" => $params["place_id"],
                "customer_id" => $customerId,
            ]);

            if (!$note && !$note->getId()) {
                throw new \Exception(p__("places", "This note does not exists."));
            }

            $note->delete();

            $payload = [
                "success" => true,
                "message" => p__("places", "Note is removed!"),
            ];
        } catch (\Exception $e) {
            $payload = [
                "eror" => true,
                "message" => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    public function _toJson($page, $address) {

        $json = [
            "id"=> $page->getId(),
            "title"=> $page->getTitle(),
            "content"=> $page->getContent(),
            "picture"=> $page->getPictureUrl(),
            "url" => $this->getUrl("places/mobile_details/index", ["value_id" => $page->getValueId(), "place_id" => $page->getId()]),
            "address" => [
                "id" => $address->getId(),
                "position" => $address->getPosition(),
                "block_id" => $address->getBlockId(),
                "label" => $address->getLabel(),
                "address" => $address->getAddress(),
                "latitude" => (float) $address->getLatitude(),
                "longitude" => (float) $address->getLongitude(),
                "show_address" => !!($address->getShowAddress()),
                "show_geolocation_button" => !!($address->getShowGeolocationButton())
            ]
        ];

        return $json;

    }


}