<?php

abstract class Catalog_Model_Product_Abstract extends Core_Model_Default
{
    
    protected $_product;
    
    public function setProduct($product) {
        $this->_product = $product;
        return $this;
    }
    
    public function getProduct() {
        return $this->_product;
    }
    
    public function save() {
        return $this;
    }
    
}