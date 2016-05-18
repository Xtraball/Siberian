<?php

class Catalog_Mobile_Setmeal_ListController extends Application_Controller_Mobile_Default {

    public function findallAction() {

        if($value_id = $this->getRequest()->getParam('value_id')) {

            $data = array("collection" => array());

            $menu = new Catalog_Model_Product();
            $offset = $this->getRequest()->getParam('offset', 0);
            $menus = $menu->findAll(array('value_id' => $value_id, 'type' => 'menu'), array(), array("offset" => $offset, "limit" => Catalog_Model_Product::DISPLAYED_PER_PAGE));

            foreach($menus as $menu) {
                $data["collection"][] = array(
                    "id" => $menu->getId(),
                    "title" => $menu->getName(),
                    "subtitle" => $menu->getFormattedPrice(),
                    "picture" => $menu->getThumbnailUrl() ? $this->getRequest()->getBaseUrl().$menu->getThumbnailUrl() : null,
                    "url" => $this->getPath("catalog/mobile_setmeal_view", array("value_id" => $value_id, "set_meal_id" => $menu->getId())),
                );
            }


//            foreach($menus as $menu) {
//                switch($this->getCurrentOptionValue()->getLayoutId()) {
//                    case 2:
//                    case 3:
//                        $data["collection"][] = array(
//                            "title" => $menu->getName(),
//                            "subtitle" => $menu->getFormattedPrice(),
//                            "picture" => $menu->getThumbnailUrl(),
//                            "url" => $this->getPath("catalog/mobile_setmeal_view", array("value_id" => $value_id, "set_meal_id" => $menu->getId())),
//                        );
//                        break;
//                    case 1:
//                    default:
//                        $data["collection"][] = array(
//                            "title" => $menu->getName(),
//                            "subtitle" => $menu->getConditions(),
//                            "picture" => $menu->getThumbnailUrl(),
//                            "url" => $this->getPath("catalog/mobile_setmeal_view", array("value_id" => $value_id, "set_meal_id" => $menu->getId())),
//                        );
//                        break;
//                }
//            }

            $data["page_title"] = $this->getCurrentOptionValue()->getTabbarName();
            $data["displayed_per_page"] = Catalog_Model_Product::DISPLAYED_PER_PAGE;

            $this->_sendHtml($data);
        }
    }

}