<?php

class Catalog_View_Product_View extends Core_View_Default
{
    protected $_product;
    
    public function getProduct() {
        if(is_null($this->_product) AND $id = $this->getRequest()->getParam('id')) {
            $product = new Catalog_Model_Product();
            $product->find($id);
            $this->_product = $product;
        }
        
        return $this->_product;
        
    }
}