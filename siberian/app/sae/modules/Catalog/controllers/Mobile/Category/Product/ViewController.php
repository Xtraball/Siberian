<?php

class Catalog_Mobile_Category_Product_ViewController extends Application_Controller_Mobile_Default {

    public function findAction() {

        if($value_id = \Siberian\Input::int($this->getRequest()->getParam('value_id'), 0, 0) AND $product_id = \Siberian\Input::int($this->getRequest()->getParam('product_id'), 0, 0)) {

            $product = new Catalog_Model_Product();
            $product->find($product_id);

            $option_value = $this->getCurrentOptionValue();

            $data = array();
            if($product->getData("type") != "menu") {

                $format = array();
                if($product->getData("type") == "format") {
                    foreach($product->getType()->getOptions() as $option) {
                        $format[] = array(
                            "id" => $option->getId(),
                            "title" => $option->getTitle(),
                            "price" => $option->getFormattedPrice()
                        );
                    }
                }

                $data = array(
                    "name" => $product->getName(),
                    "conditions" => $product->getConditions(),
                    "description" => $product->getDescription(),
                    "price" => $product->getPrice() > 0 ? $product->getFormattedPrice() : null,
                    "picture" => $product->getPictureUrl() ? $this->getRequest()->getBaseUrl().$product->getPictureUrl() : null,
                    "formats" => $format,
                    "social_sharing_active" => (bool) $option_value->getSocialSharingIsActive()
                );

            }

            $this->_sendJson($data);
        }
    }

}