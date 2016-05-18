<?php

class Catalog_Mobile_Category_ListController extends Application_Controller_Mobile_Default {

    public function findallAction() {

        if ($value_id = $this->getRequest()->getParam("value_id")) {

            try {

                $category = new Catalog_Model_Category();
                $categories = $category->findByValueId($value_id, null, true, true);
                $data = array("categories" => array());

                foreach($categories as $category) {

                    $products = array();
                    foreach($category->getProducts() as $product) {
                        $products[] = $this->_productToJson($product, $value_id);
                    }

                    usort($products, array($this, "_sortProducts"));

                    $category_data = array(
                        "id" => $category->getId(),
                        "name" => $category->getName(),
                    );

                    $children = $category->getChildren();
                    if($children->count()) {

                        foreach($category->getChildren() as $child) {
                            $child_products = array();
                            foreach($child->getProducts() as $product) {
                                $child_products[] = $products[] = $this->_productToJson($product, $value_id);
                            }

                            usort($child_products, array($this, "_sortProducts"));

                            $category_data["children"][] = array(
                                "id" => $child->getId(),
                                "name" => $child->getName(),
                                "collection" => $child_products
                            );
                        }

                         array_unshift($category_data["children"], array(
                            "id" => $child->getId(),
                            "name" => $this->_("%s - All", $category->getName()),
                            "collection" => $products
                        ));

                    } else {
                        $category_data["collection"] = $products;
                    }

                    $data["categories"][] = $category_data;
                }

                $data["page_title"] = $this->getCurrentOptionValue()->getTabbarName();
                $data["header_right_button"]["picto_url"] = $this->_getColorizedImage($this->_getImage('pictos/more.png', true), $this->getApplication()->getBlock('subheader')->getColor());

            } catch (Exception $e) {
                $data = array('error' => 1, 'message' => $e->getMessage());
            }

            $this->_sendHtml($data);
        }

    }

    protected function _productToJson($product, $value_id) {
        return array(
            "id" => $product->getId(),
            "title" => $product->getName(),
            "subtitle" => $product->getPrice() > 0 ? $product->getFormattedPrice() : strip_tags($product->getDescription()),
            "picture" => $product->getPictureUrl() ? $this->getRequest()->getBaseUrl().$product->getPictureUrl() : null,
            "url" => $this->getPath("catalog/mobile_category_product_view", array("value_id" => $value_id, "product_id" => $product->getId())),
            "position" => $product->getPosition()
        );
    }

    protected function _sortProducts($a, $b) {
        return $a["position"] > $b["position"];
    }

}