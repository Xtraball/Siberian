<?php

class Catalog_Model_Db_Table_Product_Format_Option extends Core_Model_Db_Table
{
    protected $_name = "catalog_product_format";
    protected $_primary = "option_id";

    public function loadOptionsByProductId($product_id) {
        return $this->fetchAll($this->_db->quoteInto('product_id = ?', $product_id));
    }

}