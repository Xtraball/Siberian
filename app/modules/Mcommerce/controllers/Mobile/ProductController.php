<?php

class Mcommerce_Mobile_ProductController extends Mcommerce_Controller_Mobile_Default { 

    public function findAction() { 

        if($value_id = $this->getRequest()->getParam("value_id") AND $product_id = $this->getRequest()->getParam("product_id")) {

            $product = new Catalog_Model_Product(); 
            $product->find($product_id);

            $option_value = $this->getCurrentOptionValue();
			
                /*ShowTen hjdc à revoir*/ 
				$mcommerce = new Mcommerce_Model_Mcommerce();
                $mcommerce->find(array("value_id" => $value_id));
				$mcommerce->getId();
				/*ShowTen hjdc*/			

            $data = array();

            if($product->getData("type") != "menu") {

                $current_store = $this->getStore();
                $taxRate = $current_store->getTax($product->getTaxId())->getRate();

                $minPrice = $product->getMinPrice();

                $formatGroup = array();
                $formats = $product->getType()->getOptions();
                foreach($formats as $format) {
                    $priceInclTax = $format->getPrice() * (1 + $taxRate / 100);
                    $formatGroup[] = array(
                        "id" => $format->getOptionId(),
                        "title" => $format->getTitle(),
                        "price" => $format->getPrice(),
                        "formattedPrice" => $format->getFormattedPrice(),
                        "priceInclTax" => $priceInclTax,
                        "formattedPriceInclTax" => $product->formatPrice($priceInclTax)
                    );
                }

                $optionsGroups = array();

                foreach($product->getGroups() as $group){
                    $optionsGroup = array(
                        "id" => $group->getId(),
                        "title" => $group->getTitle(),
                        "required" => $group->isRequired() === '1',
                        "options" => array(),
                        "selectedQuantity" => 1
                    );
                    foreach($group->getOptions() as $option){

                        $priceInclTax = $option->getPrice() * (1 + $taxRate / 100);

                        $optionsGroup["options"][] = array(
                            "id" => $option->getId(),
                            "optionId" => $option->getOptionId(),
                            "name" => $option->getName(),
                            "price" => (double) $option->getPrice(),
                            "formattedPrice" => $option->getPrice() > 0 ? $option->getFormattedPrice() : null,
                            "priceInclTax" => (double) $priceInclTax,
                            "formattedPriceInclTax" => $priceInclTax > 0 ? $product->formatPrice($priceInclTax) : null
                        );
                    }
                    $optionsGroups[] = $optionsGroup;
                }

                $priceInclTax = $minPrice * (1 + $taxRate / 100);

                $data = array( 
                    "product" => array(
                        "id" => $product->getId(),
                        "name" => $product->getName(),
						/*ShowTen hjdc*/ "show_ten" =>$mcommerce->getShowTen(),/*ShowTen hjdc*/
						/*MaskQtyOpt hjdc*/ "mask_qty_opt" =>$mcommerce->getMaskQtyOpt(),/*MaskQtyOpt hjdc*/
                        "conditions" => $product->getConditions(),
                        "description" => $product->getDescription(),
                        "shortDescription" => strip_tags($product->getDescription()),
                        "price" => (float) $minPrice > 0 ? $minPrice:null,
                        "formattedPrice" => $minPrice > 0 ? $product->formatPrice($minPrice):null,
                        "priceInclTax" => (double) $priceInclTax,
                        "formattedPriceInclTax" => $priceInclTax > 0 ? $product->formatPrice($priceInclTax) : null,
                        "minPrice" => (float) $minPrice,
                        "formattedMinPrice" => $minPrice > 0 ? $product->formatPrice($minPrice) : null,
                        "picture" => $product->getLibraryPictures(true, $this->getRequest()->getBaseUrl()),
                        "optionsGroups" => $optionsGroups,
                        "formatGroups" => $formatGroup,
                        "social_sharing_active" => $option_value->getSocialSharingIsActive()
                    ),
                    "page_title" => $product->getName()
                );

            }


					
            $this->_sendHtml($data);
        }
    }

}