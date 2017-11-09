<?php

class Mcommerce_Mobile_CategoryController extends Mcommerce_Controller_Mobile_Default {

    public function findallAction() {

        if($value_id = $this->getRequest()->getParam('value_id')) {

            try {

                $category_id = $this->getRequest()->getParam('category_id');
                $current_category = new Folder_Model_Category();

                $offset = $this->getRequest()->getParam('offset', 0);

                if($category_id) {
                    $current_category->find($category_id, 'category_id');
                }

                $object = $this->getCurrentOptionValue()->getObject();

                if(!$object->getId() OR ($current_category->getId() AND $current_category->getRootCategoryId() != $object->getRootCategoryId())) {
                    throw new Exception($this->_('An error occurred during process. Please try again later.'));
                }

                if(!$current_category->getId()) {
                    $current_category = $object->getRootCategory();
                }

                $data = array("collection" => array());

                $subcategories = $current_category->getChildren($offset);

                foreach($subcategories as $subcategory) {
                    $data["collection"][] = array(
                        "title" => $subcategory->getTitle(),
                        "subtitle" => $subcategory->getSubtitle(),
                        "picture" => $subcategory->getPictureUrl() ? $this->getRequest()->getBaseUrl().$subcategory->getPictureUrl() : null,
                        "url" => $this->getPath("mcommerce/mobile_category", array("value_id" => $value_id, "category_id" => $subcategory->getId()))
                    );
                }

                //TMP : removing pagination on product list
                $offset = null;
                $products = $current_category->getProducts($offset);
                $color = $this->getApplication()->getBlock('background')->getImageColor();

                $current_store = $this->getStore();

                foreach($products as $product) {

                    $taxRate = $current_store->getTax($product->getTaxId())->getRate();

                    $picture = null;
                    if($image = $product->getLibraryPictures(false)) {
                        $picture = $this->getRequest()->getBaseUrl().$image["url"];
                    }

                    $productPrice = $product->getPrice();
                    $displayPrice = Mcommerce_Model_Utility::displayPrice($productPrice, $taxRate);

                    $data["collection"][] = array(
                        "title" => $product->getName(),
                        "subtitle" => $product->getPrice() > 0 ? $displayPrice : strip_tags(html_entity_decode($product->getDescription())),
                        "picture" => $picture,
                        "url" => $product->getPath("mcommerce/mobile_product", array("value_id" => $value_id, "product_id" => $product->getId()))
                    );
                }

                $mcommerce = new Mcommerce_Model_Mcommerce();
                $mcommerce->find(array("value_id" => $value_id));
                if($mcommerce->getId()) {
                    if($mcommerce->getShowSearch() == 1 ) {
                        $data["show_search"] = 1;
                    }
                }

                $data["cover"] = array(
                    "title" => $this->_($current_category->getTitle()),
                    "subtitle" => $current_category->getSubtitle(),
                    "picture" => $current_category->getPictureUrl() ? $this->getRequest()->getBaseUrl().$current_category->getPictureUrl() : null
                );

                $data["page_title"] = $this->_($current_category->getTitle());
                $data["displayed_per_page"] = Folder_Model_Category::DISPLAYED_PER_PAGE;

            }
            catch(Exception $e) {
                $data = array('error' => 1, 'message' => $e->getMessage());
            }

            $this->_sendHtml($data);

        }

    }

}