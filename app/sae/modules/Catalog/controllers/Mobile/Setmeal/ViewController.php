<?php

class Catalog_Mobile_Setmeal_ViewController extends Application_Controller_Mobile_Default {

    public function findAction() {

        if($value_id = $this->getRequest()->getParam('value_id') AND $set_meal_id = $this->getRequest()->getParam('set_meal_id')) {

            $set_meal = new Catalog_Model_Product();
            $set_meal->find($set_meal_id);

            $option_value = $this->getCurrentOptionValue();

            $data = array();

            if($set_meal->getData("type") == "menu") {

                $data = array(
                    "name" => $set_meal->getName(),
                    "conditions" => $set_meal->getConditions(),
                    "description" => $set_meal->getDescription(),
                    "price" => $set_meal->getPrice() > 0 ? $set_meal->getFormattedPrice() : null,
                    "picture" => $set_meal->getPictureUrl() ? $this->getRequest()->getBaseUrl().$set_meal->getPictureUrl() : null,
                    "social_sharing_active" => $option_value->getSocialSharingIsActive()
                );

            }

            $this->_sendHtml($data);
        }
    }

}