<?php

use Siberian\Json;
use Siberian\Exception;

/**
 * Class Places_Mobile_ListController
 *
 * @version 4.15.7
 */
class Places_Mobile_ListController extends Application_Controller_Mobile_Default
{
    /**
     *
     */
    public function findOneAction()
    {
        try {
            $request = $this->getRequest();

            $placeId = $request->getParam("place_id", null);
            $optionValue = $this->getCurrentOptionValue();

            $place = (new Places_Model_Place())
                ->find($placeId);

            if (!$place->getId()) {
                throw new Exception(__("This place do not exists!"));
            }

            $place = $place->toJson($optionValue, $request->getBaseUrl());

            $payload = [
                "success" => true,
                "social_sharing_active" => (boolean) $optionValue->getSocialSharingIsActive(),
                "page_title" => "title",
                "place" => $place["embed_payload"],
                "page" => $place["embed_payload"]["page"],
                "blocks" => $place["embed_payload"]["blocks"],
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function findallAction()
    {
        try {
            $request = $this->getRequest();

            $isMaps = $request->getParam("maps", false);
            $limit = $request->getParam("limit", 20);
            $offset = $request->getParam("offset", 0);
            $fulltext = $request->getParam("fulltext", null);
            $categories = $request->getParam("categories", []);

            $position = [
                "latitude" => $request->getParam("latitude", 0),
                "longitude" => $request->getParam("longitude", 0)
            ];

            $optionValue = $this->getCurrentOptionValue();
            $valueId = $optionValue->getId();

            // Default sort is distance, model will determine if location is sent and sort by alpha ion fallback!
            $sortingType = "distance";
            $params = [
                "offset" => $offset,
                "limit" => $limit,
                "fulltext" => $fulltext,
                "categories" => $categories,
                "sortingType" => $sortingType,
            ];

            if ($isMaps) {
                $params["offset"] = 0;
                $params["limit"] = null;
            }

            /**
             * @var $places Places_Model_Place[]
             */
            $places = (new Places_Model_Place())
                ->findAllWithFilters($valueId, [
                    'search_by_distance' => true,
                    'latitude' => $position['latitude'],
                    'longitude' => $position['longitude'],
                ], $params);

            $countParams = $params;
            unset($countParams["offset"]);
            unset($countParams["limit"]);
            $count = (new Places_Model_Place())
                ->findAllWithFilters($valueId, [
                    'search_by_distance' => true,
                    'latitude' => $position['latitude'],
                    'longitude' => $position['longitude'],
                ], $countParams);

            $collection = [];
            foreach ($places as $place) {
                $collection[] = $place->toJson($optionValue, $request->getBaseUrl());
            }

            $payload = [
                "success" => true,
                "sortingType" => $sortingType,
                "page_title" => $optionValue->getTabbarName(),
                "displayed_per_page" => sizeof($collection),
                "social_sharing_active" => (boolean) $optionValue->getSocialSharingIsActive(),
                "total" => $count->count(),
                "places" => $collection
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function fetchSettingsAction ()
    {
        try {
            $optionValue = $this->getCurrentOptionValue();

            // Set default settings
            $defaults = [
                "default_page" => (string) "places",
                "default_layout" => (string) "place-100",
                "distance_unit" => (string) "km",
                "listImagePriority" => (string) "thumbnail",
                "defaultPin" => (string) "pin",
                "categories" => []
            ];

            if (!$optionValue->getId()) {
                $settings = $defaults;
            } else {
                try {
                    $settings = Json::decode($optionValue->getSettings());
                } catch (\Exception $e) {
                    $settings = $defaults;
                }

                $categories = (new Places_Model_Category())
                    ->findAll(["value_id" => $optionValue->getId()], "position ASC");

                $settings["categories"] = [];
                foreach ($categories as $category) {
                    $settings["categories"][] = [
                        'id' => (integer) $category->getId(),
                        'title' => (string) $category->getTitle(),
                        'subtitle' => (string) $category->getSubtitle(),
                        'picture' => (string) $category->getPicture(),
                    ];
                }
            }
            
            $payload = [
                "success" => true,
                "settings" => $settings,
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
            ];
        }
        
        $this->_sendJson($payload);
    }

    /**
     * @deprecated only act as fallback
     */
    public function settingsAction()
    {
        if ($value_id = $this->getRequest()->getParam("value_id")) {
            $html = ["tags" => []];
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
