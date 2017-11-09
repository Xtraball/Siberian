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
                        "id"        => (integer) $category->getId(),
                        "name"      => $category->getName(),
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
                                "id"            => (integer) $child->getId(),
                                "name"          => $child->getName(),
                                "collection"    => $child_products
                            );
                        }

                         array_unshift($category_data["children"], array(
                            "id"            => (integer) $child->getId(),
                            "name"          => __("%s - All", $category->getName()),
                            "collection"    => $products
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

            $this->_sendJson($data);
        }

    }

    protected function _productToJson($product, $value_id) {

        $baseUrl = '';
        if ($this->getRequest()) {
            $baseUrl = $this->getRequest()->getBaseUrl();
        }

        $format = array();
        if($product->getData('type') === 'format') {
            foreach($product->getType()->getOptions() as $option) {
                $format[] = [
                    'id' => (integer) $option->getId(),
                    'title' => $option->getTitle(),
                    'price' => $option->getFormattedPrice()
                ];
            }
        }

        $picture_b64 = null;
        if($product->getPictureUrl()) {
            $picture = Core_Model_Directory::getBasePathTo($product->getPictureUrl());
            $picture_b64 = Siberian_Image::getForMobile($baseUrl, $picture, null, 2048, 2048);
        }

        $embed_payload = [
            'name' => $product->getName(),
            'conditions' => $product->getConditions(),
            'description' => $product->getDescription(),
            'price' => $product->getPrice() > 0 ? $product->getFormattedPrice() : null,
            'picture' => $picture_b64,
            'formats' => $format,
            'social_sharing_active' => (boolean) $this->getCurrentOptionValue()->getSocialSharingIsActive()
        ];

        return [
            'id' => (integer) $product->getId(),
            'title' => $product->getName(),
            'subtitle' => strip_tags($product->getDescription()).($product->getPrice() > 0 ? "<br>".$product->getFormattedPrice() : ""),
            'picture' => $picture_b64,
            'url' => $this->getPath("catalog/mobile_category_product_view", array("value_id" => $value_id, "product_id" => $product->getId())),
            'position' => $product->getPosition(),
            'embed_payload' => $embed_payload
        ];
    }

    protected function _sortProducts($a, $b) {
        return $a["position"] > $b["position"];
    }

}
