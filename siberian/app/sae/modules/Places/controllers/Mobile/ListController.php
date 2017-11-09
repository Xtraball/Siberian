<?php

class Places_Mobile_ListController extends Application_Controller_Mobile_Default {

    public function findallAction() {

        try {
            $request = $this->getRequest();
            if($value_id = $request->getParam("value_id")) {

                $is_maps = $request->getParam("maps", false);

                $limit = $request->getParam("limit", 100);
                $offset = $request->getParam("offset", 0);

                $position = array(
                    "latitude" => $request->getParam("latitude"),
                    "longitude" => $request->getParam("longitude")
                );

                $value = $this->getCurrentOptionValue();

                $params = array(
                    "offset" => $offset,
                    "limit" => $limit
                );

                $repository = new Cms_Model_Application_Page();
                if(!$is_maps) {
                    $order_places = $value->getMetadataValue("places_order");

                    if ($request->getParam("by_name")) {
                        $pages = $repository->findAllOrderedByLabel($value->getId(), $params);
                    } else {
                        if ($order_places) {
                            $pages = $repository->findAll(
                                array(
                                    "value_id" => $value_id
                                ),
                                null,
                                $params
                            );
                        } else {
                            $pages = $repository->findAllOrderedByRank($value->getId(), $params);
                        }
                    }
                } else {
                    $pages = $repository->findAll(array("value_id" => $value_id));
                }


                $place_list = array();

                foreach($pages as $page) {
                    $place = new Places_Model_Place();
                    $place->setPage($page);
                    // Get the json representation of the place
                    if(!$is_maps) {
                        $representation = $place->asJson($this, $position, $value, $request->getBaseUrl());
                    } else {
                        $representation = $place->asMapJson($this, $position, $value, $request->getBaseUrl());
                    }

                    // append it to the places" list
                    if($representation !== false) {
                        $place_list[] = $representation;
                    }

                }

                if ($this->getCurrentOptionValue()->getMetadataValue("places_order_alpha")) {
                    usort($place_list, array("Places_Model_Place", "sortPlacesByLabel"));
                } else if ($this->getCurrentOptionValue()->getMetadataValue("places_order")) {
                    // Order places by distance to user, if and the position is set the places_order option is activated
                    if ($position["latitude"] && $position["longitude"]) {
                        usort($place_list, array("Places_Model_Place", "sortPlacesByDistance"));
                    }
                }

                $option = $this->getCurrentOptionValue();

                $payload = array(
                    "success"       => true,
                    "page_title"    => $option->getTabbarName(),
                    "displayed_per_page"    => sizeof($place_list),
                    "places"        => $place_list
                );

            } else {
                throw new Siberian_Exception(__("Missing parameters."));
            }


        } catch(Exception $e) {
            $payload = array(
                "error"     => true,
                "message"   => __("An error occurred during process. Please try again later.")
            );
        }

        $this->_sendJson($payload);
    }

    public function searchAction()
    {
        $request = $this->getRequest();
        if ($search_criteria = json_decode($this->getRequest()->getParam("search"))) {
            try {
                $value_id = $this->getRequest()->getParam("value_id");
                $option = $this->getCurrentOptionValue();
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
                    $representation = $place->asJson($this, $position, $option, $request->getBaseUrl());
                    // append it to the places' list
                    $place_list[] = $representation;
                }
                if ($this->getCurrentOptionValue()->getMetadataValue('places_order_alpha')) {
                    usort($place_list, array('Places_Model_Place', 'sortPlacesByLabel'));
                } else if ($this->getCurrentOptionValue()->getMetadataValue('places_order')) {
                    // Order places by distance to user, if and the position is set the places_order option is activated
                    if ($position['latitude'] && $position['longitude']) {
                        usort($place_list, array('Places_Model_Place', 'sortPlacesByDistance'));
                    }
                }

                $data["page_title"] = $option->getTabbarName();
                $data = array("places" => $place_list);
            } catch (Exception $e) {
                $data = array('error' => 1, 'message' => 'An error occurred during process. Please try again later.');
            }
            $this->_sendJson($data);
        }
    }

    public function searchv2Action() {

        try {

            $request = $this->getRequest();

            if ($search_criteria = Siberian_Json::decode($request->getRawBody())) {

                Zend_Debug::dump($search_criteria);

                $value_id = $request->getParam("value_id");

                $option = $this->getCurrentOptionValue();

                $position = array(
                    "latitude"  => $search_criteria["latitude"],
                    "longitude" => $search_criteria["longitude"]
                );

                $repository = new Places_Model_Place();
                $pages = $repository->search($search_criteria["search"], $value_id);
                $place_list = array();

                foreach ($pages as $page) {
                    $place = new Places_Model_Place();
                    $place->setPage($page);
                    // Get the json representation of the place
                    $representation = $place->asJson($this, $position, $option, $request->getBaseUrl());
                    // append it to the places" list
                    $place_list[] = $representation;
                }


                if ($this->getCurrentOptionValue()->getMetadataValue("places_order_alpha")) {
                    usort($place_list, array("Places_Model_Place", "sortPlacesByLabel"));
                } else if ($this->getCurrentOptionValue()->getMetadataValue("places_order")) {
                    // Order places by distance to user, if and the position is set the places_order option is activated
                    if ($position["latitude"] && $position["longitude"]) {
                        usort($place_list, array("Places_Model_Place", "sortPlacesByDistance"));
                    }
                }

                $payload = array(
                    "succes" => true,
                    "page_title" => $option->getTabbarName(),
                    "places" => $place_list
                );

            } else {
                throw new Siberian_Exception(__("The search request is empty."));
            }

        } catch(Exception $e) {
            $payload = array(
                "error"     => true,
                "message"   => __("An error occurred during process. Please try again later.")
            );
        }

        $this->_sendJson($payload);

    }

    /**
     * @deprecated in Siberian 5.0 only act as fallback
     */
    public function settingsAction() {
        if ($value_id = $this->getRequest()->getParam("value_id")) {
            $html = array("tags" => array());
            $option_value = new Application_Model_Option_Value();
            $option_value->find($value_id);
            $metadata = $option_value->getMetadatas();
            $tags = $option_value->getOwnTags(new Cms_Model_Application_Page());

            foreach ($metadata as $meta) {
                $html[$meta->getCode()] = $meta->getPayload();
            }

            foreach ($tags as $tag) {
                $html["tags"][] = strtolower(trim($tag->getName()));
            }

            $html["tags"] = array_unique($html["tags"]);

            $this->_sendJson($html);
        }
    }
}
