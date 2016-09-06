<?php

class Places_Mobile_ListController extends Application_Controller_Mobile_Default
{

    public function findallAction()
    {

        if ($value_id = $this->getRequest()->getParam('value_id')) {

            $latitude = $this->getRequest()->getParam('latitude');
            $longitude = $this->getRequest()->getParam('longitude');
            $lat_a = null;
            $lon_a = null;

            if ($latitude && $longitude) {
                // order by distance from specified latitude/longitude

                $rad = pi() / 180;
                $lat_a = $latitude * $rad;
                $lon_a = $longitude * $rad;
            }

            try {

                $pageRepository = new Cms_Model_Application_Page();
                $pages = $pageRepository->findAll(array('value_id' => $value_id));

                $json = array();

                foreach ($pages as $page) {

                    $blocks = $page->getBlocks();
                    $data = array("blocks" => array());

                    foreach ($blocks as $block) {

                        if ($block->getType() == "address") {
                            $place = $this->_toJson($page, $block);
                            // only one address per place
                            $place['show_image'] = $page->getMetadataValue('show_image');
                            $place['show_titles'] = $page->getMetadataValue('show_titles');

                            if ($lat_a && $lon_a && $block->getLatitude() && $block->getLongitude()) {
                                // calculate distance from specified latitude/longitude
                                $lat_b = $block->getLatitude() * $rad;
                                $lon_b = $block->getLongitude() * $rad;

                                $distance = 2 * asin(sqrt(pow(sin(($lat_a - $lat_b) / 2), 2) + cos($lat_a) * cos($lat_b) * pow(sin(($lon_a - $lon_b) / 2), 2)));
                                $distance *= 6371000 * 10000;
                                $place["distance"] = round($distance);
                            }

                            $json[] = $place;

                            break;
                        }

                    }

                }

                if ($latitude && $longitude) {
                    // order by distance from specified latitude/longitude
                    usort($json, array('Places_Model_Place', 'sortPlacesByDistance'));
                }
                $data = array("places" => $json);
                $option = $this->getCurrentOptionValue();
                $data["page_title"] = $option->getTabbarName();

            } catch (Exception $e) {
                $data = array('error' => 1, 'message' => $e->getMessage());
            }

        } else {
            $data = array('error' => 1, 'message' => 'An error occurred during process. Please try again later.');
        }

        $this->_sendHtml($data);

    }

    protected function _toJson($page, $address)
    {

        $url = $this->getUrl("places/mobile_details/index", array("value_id" => $page->getValueId(), "place_id" => $page->getId()));
        if ($this->getApplication()->useIonicDesign()) {
            $url = $this->getPath("cms/mobile_page_view/index", array("value_id" => $page->getValueId(), "page_id" => $page->getId()));
        }

        $json = array(
            "id" => $page->getId(),
            "title" => $page->getTitle(),
            "subtitle" => $page->getContent(),
            "picture" => $page->getPictureUrl() ? $this->getRequest()->getBaseUrl() . $page->getPictureUrl() : null,
            "url" => $url,
            "address" => array(
                "id" => $address->getId(),
                "position" => $address->getPosition(),
                "block_id" => $address->getBlockId(),
                "label" => $address->getLabel(),
                "address" => $address->getAddress(),
                "latitude" => $address->getLatitude(),
                "longitude" => $address->getLongitude(),
                "show_address" => $address->getShowAddress(),
                "show_geolocation_button" => $address->getGeolocationButton())
        );

        return $json;

    }

}
