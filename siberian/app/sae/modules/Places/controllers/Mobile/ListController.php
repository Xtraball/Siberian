<?php

/**
 * Class Places_Mobile_ListController
 */
class Places_Mobile_ListController extends Application_Controller_Mobile_Default
{
    /**
     *
     */
    public function findallAction()
    {
        try {
            $request = $this->getRequest();

            $isMaps = $request->getParam("maps", false);
            $limit = $request->getParam("limit", 25);
            $offset = $request->getParam("offset", 0);
            $fulltext = $request->getParam("fulltext", null);
            $categories = $request->getParam("categories", []);

            $position = [
                "latitude" => $request->getParam("latitude", 0),
                "longitude" => $request->getParam("longitude", 0)
            ];

            // TESTING
            $position = ["latitude" => 43.5462231, "longitude" => 1.5052359];

            $optionValue = $this->getCurrentOptionValue();
            $valueId = $optionValue->getId();

            $sortingType = 'date';
            if ($optionValue->getMetadataValue("places_order_alpha")) {
                $sortingType = 'alpha';
            } else if ($optionValue->getMetadataValue("places_order")) {
                $sortingType = 'distance';
            }

            $params = [
                "offset" => $offset,
                "limit" => $limit,
                "fulltext" => $fulltext,
                "categories" => $categories,
                "sortingType" => $sortingType,
            ];

            // Fetch places!

            /**
             * @var $places Places_Model_Place[]
             */
            $places = [];
            if (!$isMaps) {
                $places = (new Places_Model_Place())
                    ->findAllWithFilters($valueId, [
                    'search_by_distance' => true,
                    'latitude' => $position['latitude'],
                    'longitude' => $position['longitude'],
                ], $params);
            } else {
                $places = (new Places_Model_Place())
                    ->findAll(["value_id" => $valueId]);
            }

            $collection = [];
            foreach ($places as $place) {
                $collection[] = $place->toJson($optionValue, $request->getBaseUrl());
            }

            $payload = [
                "success" => true,
                "sortingType" => $sortingType,
                "page_title" => $optionValue->getTabbarName(),
                "displayed_per_page" => sizeof($collection),
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
     * @deprecated in Siberian 5.0 only act as fallback
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
