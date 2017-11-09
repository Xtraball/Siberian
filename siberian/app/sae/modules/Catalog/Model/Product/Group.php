<?php

class Catalog_Model_Product_Group extends Core_Model_Default {

    protected $_options;

    public function __construct($datas = array()) {
        parent::__construct($datas);
        $this->_db_table = 'Catalog_Model_Db_Table_Product_Group';
    }

    public function save() {

        parent::save();

        if($this->getNewOption() AND !$this->getIsDeleted()) {
            $new_option_ids = array();
            foreach($this->getNewOption() as $option_id => $new_option) {
                $is_new = false;
                $option = new Catalog_Model_Product_Group_Option();
                if(stripos($option_id, 'new') === false) $new_option['option_id'] = $option_id;
                else $is_new = true;
                $new_option['group_id'] = $this->getId();
                $option->addData($new_option)->save();

                if($is_new) $new_option_ids[] = $option->getId();
            }
            $this->setNewOptionIds($new_option_ids);
        }

    }

    public function isRequired() {
        return $this->getData('is_required');
    }

    public function getOptions() {

        if(!$this->_options) {
            $option = new Catalog_Model_Product_Group_Option();
            $this->_options = $option->findAll(array('group_id' => $this->getId()));
        }

        return $this->_options;

    }

}