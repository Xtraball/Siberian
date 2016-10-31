<?php

class Places_Mobile_ListController extends Application_Controller_Mobile_Default {

    public function findallAction() {

        if($value_id = $this->getRequest()->getParam('value_id')) {

            try {
                $position = array(
                    'latitude' => $this->getRequest()->getParam('latitude'),
                    'longitude' => $this->getRequest()->getParam('longitude')
                );

                $repository = new Cms_Model_Application_Page();
                $pages = $repository->findAll(array('value_id' => $value_id));

                $place_list = array();

                foreach($pages as $page) {
                    $place = new Places_Model_Place();
                    $place->setPage($page);
                    // Get the json representation of the place
                    $representation = $place->asJson($this, $position);
                    // append it to the places' list
                    $place_list[] = $representation;
                }

                // Order places by distance to user
                if ($position['latitude'] && $position['longitude']) {
                    usort ( $json , array('Places_Model_Place','sortPlacesByDistance'));
                }

                $option = $this->getCurrentOptionValue();
                $data["page_title"] = $option->getTabbarName();
                $data = array("places" => $place_list);

            }
            catch(Exception $e) {
                $data = array('error' => 1, 'message' => 'An error occurred during process. Please try again later.');
            }
	
	    $this->_sendHtml($data);
        }
    }

    public function searchAction()
    {
        if ($search_criteria = json_decode($this->getRequest()->getParam("search"))) {
            try {
                $value_id = $this->getRequest()->getParam("value_id");

                $position = array(
                    'latitude' => $this->getRequest()->getParam('latitude'),
                    'longitude' => $this->getRequest()->getParam('longitude')
                );

                $repository = new Places_Model_Place();
                $pages = $repository->search($search_criteria, $value_id);
                $place_list = array();

                foreach ($pages as $page) {
                    $place = new Places_Model_Place();
                    $place->setPage($page);
                    // Get the json representation of the place
                    $representation = $place->asJson($this, $position);
                    // append it to the places' list
                    $place_list[] = $representation;
                }

                // Order places by distance to user
                if ($position['latitude'] && $position['longitude']) {
                    usort($json, array('Places_Model_Place', 'sortPlacesByDistance'));
                }

                $option = $this->getCurrentOptionValue();
                $data["page_title"] = $option->getTabbarName();
                $data = array("places" => $place_list);

            } catch (Exception $e) {
                $data = array('error' => 1, 'message' => 'An error occurred during process. Please try again later.');
	    }

	    $this->_sendHtml($data);
        }
    }

    public function settingsAction() {
        if ($value_id = $this->getRequest()->getParam("value_id")) {
            $html = array('tags' => array());
            $option_value = new Application_Model_Option_Value();
            $option_value->find($value_id);
            $metadata = $option_value->getMetadatas();
            $tags = $option_value->getOwnTags(new Cms_Model_Application_Page());

            foreach ($metadata as $meta) {
                $html[$meta->getCode()] = $meta->getPayload();
            }

            foreach ($tags as $tag) {
                $html['tags'][] = strtolower(trim($tag->getName()));
            }

            $html['tags'] = array_unique($html['tags']);

            $this->_sendHtml($html);
        }
    }
}
