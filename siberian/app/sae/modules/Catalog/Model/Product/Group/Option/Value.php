<?php

class Catalog_Model_Product_Group_Option_Value extends Catalog_Model_Product_Group_Option {

    public function __construct($datas = array()) {
        parent::__construct($datas);
        $this->_db_table = 'Catalog_Model_Db_Table_Product_Group_Option_Value';
    }

    public function findAllOptions($group_id, $product_id) {
        return $this->getTable()->findAllOptions($group_id, $product_id);
    }

}