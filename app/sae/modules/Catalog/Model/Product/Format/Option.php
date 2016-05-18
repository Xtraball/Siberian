<?php

class Catalog_Model_Product_Format_Option extends Core_Model_Default
{

    protected $_outlets;
    protected $_pos_datas;

    public function __construct($datas = array()) {
        parent::__construct($datas);
        $this->_db_table = 'Catalog_Model_Db_Table_Product_Format_Option';
    }

    public function findByProductId($product_id) {
        return $this->getTable()->loadOptionsByProductId($product_id);
    }

    public function save() {

        $price = trim(str_replace(array(Core_Model_Language::getCurrencySymbol(), ' '), '', $this->getData('price')));
        $filter = new Zend_Filter_LocalizedToNormalized();
        $filter->setOptions(array('locale' => Zend_Registry::get('Zend_Locale')));
        $this->setData('price', $filter->filter($price));
        parent::save();

        return $this;
    }
}