<?php

class Mcommerce_Mobile_ProductController extends Mcommerce_Controller_Mobile_Default {

    public function findAction() {

        if($value_id = $this->getRequest()->getParam("value_id") AND $product_id = $this->getRequest()->getParam("product_id")) {

            $product = new Catalog_Model_Product();
            $product->find($product_id);

            $option_value = $this->getCurrentOptionValue();

            $data = array();

            if($product->getData("type") != "menu") {

                $current_store = $this->getStore();
                $taxRate = $current_store->getTax($product->getTaxId())->getRate();

                $minPrice = $product->getMinPrice();

                $formatGroup = array();
                $formats = $product->getType()->getOptions();
                foreach($formats as $format) {
                    $priceInclTax = $format->getPrice() * (1 + $taxRate / 100);

                    $displayPrice = Mcommerce_Model_Utility::displayPrice($format->getPrice(), 0);
                    $displayPriceInclTax = Mcommerce_Model_Utility::displayPrice($format->getPrice(), $taxRate);

                    /** @wip #1688 */
                    $formatGroup[] = array(
                        "id" => $format->getOptionId(),
                        "title" => $format->getTitle(),
                        "price" => $format->getPrice(),
                        //"formattedPrice" => $format->getFormattedPrice(),
                        "formattedPrice" => $displayPrice,
                        "priceInclTax" => $priceInclTax,
                        //"formattedPriceInclTax" => $product->formatPrice($priceInclTax)
                        "formattedPriceInclTax" => $displayPriceInclTax
                    );
                }

                $optionsGroups = array();
                $choicesGroups = array();
                $product_groups = $product->getGroups();
                $product_choices = $product->getChoices();

                foreach($product_groups as $group){
                    $optionsGroup = array(
                        "id" => $group->getId(),
                        "title" => $group->getTitle(),
                        "required" => $group->isRequired() === '1',
                        "options" => array(),
                        "selectedQuantity" => 1
                    );
                    foreach($group->getOptions() as $option){

                        $priceInclTax = $option->getPrice() * (1 + $taxRate / 100);

                        $displayPrice = Mcommerce_Model_Utility::displayPrice($option->getPrice(), 0);
                        $displayPriceInclTax = Mcommerce_Model_Utility::displayPrice($option->getPrice(), $taxRate);

                        /** @wip #1688 */
                        $optionsGroup["options"][] = array(
                            "id" => $option->getId(),
                            "optionId" => $option->getOptionId(),
                            "name" => $option->getName(),
                            "price" => (double) $option->getPrice(),
                            //"formattedPrice" => $option->getPrice() > 0 ? $option->getFormattedPrice() : null,
                            "formattedPrice" => $option->getPrice() > 0 ? $displayPrice : null,
                            "priceInclTax" => (double) $priceInclTax,
                            //"formattedPriceInclTax" => $priceInclTax > 0 ? $product->formatPrice($priceInclTax) : null
                            "formattedPriceInclTax" => $priceInclTax > 0 ? $displayPriceInclTax : null
                        );
                    }
                    $optionsGroups[] = $optionsGroup;
                }
                foreach($product_choices as $choice){
                    $choicesGroup = array(
                        "id" => $choice->getGroupId(),
                        "title" => $choice->getTitle(),
                        "required" => $choice->isRequired() === '1',
                        "options" => array()
                    );
                    foreach($choice->getOptions($product_id) as $option){
                        $choicesGroup["options"][] = array(
                            "id" => $option->getOptionId(),
                            "optionId" => $option->getOptionId(),
                            "name" => $option->getName(),
                            "selected" => false
                        );
                    }
                    $choicesGroups[] = $choicesGroup;
                }

                $priceInclTax = $minPrice * (1 + $taxRate / 100);

                $displayPrice = Mcommerce_Model_Utility::displayPrice($minPrice, 0);
                $displayPriceInclTax = Mcommerce_Model_Utility::displayPrice($minPrice, $taxRate);

                /** @wip #1688 */
                $data = array(
                    "product" => array(
                        "id" => $product->getId(),
                        "name" => $product->getName(),
                        "conditions" => $product->getConditions(),
                        "description" => $product->getDescription(),
                        "shortDescription" => strip_tags($product->getDescription()),
                        "price" => (float) $minPrice > 0 ? $minPrice:null,
                        //"formattedPrice" => $minPrice > 0 ? $product->formatPrice($minPrice):null,
                        "formattedPrice" => $minPrice > 0 ? $displayPrice : null,
                        "priceInclTax" => (double) $priceInclTax,
                        //"formattedPriceInclTax" => $priceInclTax > 0 ? $product->formatPrice($priceInclTax) : null,
                        "formattedPriceInclTax" => $priceInclTax > 0 ? $displayPriceInclTax : null,
                        "minPrice" => (float) $minPrice,
                        "formattedMinPrice" => $minPrice > 0 ? $product->formatPrice($minPrice) : null,
                        "picture" => $product->getLibraryPictures(true, $this->getRequest()->getBaseUrl()),
                        "optionsGroups" => $optionsGroups,
                        "choicesGroups" => $choicesGroups,
                        "formatGroups" => $formatGroup,
                        "social_sharing_active" => (boolean) $option_value->getSocialSharingIsActive()
                    ),
                    "page_title" => $product->getName()
                );

            }

            $this->_sendHtml($data);
        }
    }

}