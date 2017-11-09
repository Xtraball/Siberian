<?php

class Catalog_View_Pos_Product_Edit_Products_Row extends Admin_View_Default
{

    protected $_product;

    public function getProduct() {
        return $this->_product;
    }

    public function setProduct($product) {
        $this->_product = $product;
        return $this;
    }

}