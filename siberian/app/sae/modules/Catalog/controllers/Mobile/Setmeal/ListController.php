<?php

class Catalog_Mobile_Setmeal_ListController extends Application_Controller_Mobile_Default {

    public function findallAction() {

        try {

            $request = $this->getRequest();

            if($value_id = $request->getParam("value_id")) {

                $baseUrl = '';
                if ($this->getRequest()) {
                    $baseUrl = $this->getRequest()->getBaseUrl();
                }

                $option_value = $this->getCurrentOptionValue();

                $collection = array();

                $menu = new Catalog_Model_Product();
                $offset = $request->getParam("offset", 0);
                $menus = $menu->findAll(array(
                        "value_id" => $value_id,
                        "type" => "menu"
                    ),
                    array(),
                    array(
                        "offset" => $offset,
                        "limit" => Catalog_Model_Product::DISPLAYED_PER_PAGE
                    )
                );

                foreach($menus as $menu) {

                    $thumbnail_b64 = null;
                    if($menu->getThumbnailUrl()) {
                        $picture = Core_Model_Directory::getBasePathTo($menu->getThumbnailUrl());
                        $thumbnail_b64 = Siberian_Image::getForMobile($baseUrl, $picture, null, 2048, 2048);
                    }

                    $picture_b64 = null;
                    if($menu->getPictureUrl()) {
                        $picture = Core_Model_Directory::getBasePathTo($menu->getPictureUrl());
                        $picture_b64 = Siberian_Image::getForMobile($baseUrl, $picture, null, 2048, 2048);
                    }

                    $collection[] = array(
                        "id"        => $menu->getId() * 1,
                        "title"     => $menu->getName(),
                        "subtitle"  => $menu->getPrice() > 0 ? $menu->getFormattedPrice() : null,
                        "picture"   => $thumbnail_b64,
                        "url"       => $this->getPath("catalog/mobile_setmeal_view", array(
                                            "value_id" => $value_id, "set_meal_id" => $menu->getId())),
                        "embed_payload" => array(
                            "name"          => $menu->getName(),
                            "conditions"    => $menu->getConditions(),
                            "description"   => $menu->getDescription(),
                            "price"         => $menu->getPrice() > 0 ? $menu->getFormattedPrice() : null,
                            "picture"       => $picture_b64,
                            "social_sharing_active" => (boolean) $option_value->getSocialSharingIsActive()
                        )
                    );
                }

                $payload = array(
                    "success"               => true,
                    "collection"            => $collection,
                    "page_title"            => $option_value->getTabbarName(),
                    "displayed_per_page"    => Catalog_Model_Product::DISPLAYED_PER_PAGE
                );

            } else {
                throw new Siberian_Exception(__("Missing parameter value_id."));
            }

        } catch(Exception $e) {

            $payload = array(
                "error" => true,
                "message" => $e->getMessage()
            );

        }

        $this->_sendJson($payload);


    }

}