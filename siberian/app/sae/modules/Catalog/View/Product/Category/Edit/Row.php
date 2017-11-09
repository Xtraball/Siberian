<?php

class Catalog_View_Pos_Category_Edit_Row extends Admin_View_Default
{

    protected $_category;

    public function getCategory() {
        return $this->_category;
    }

    public function setCategory($category) {
        $this->_category = $category;
        return $this;
    }

}