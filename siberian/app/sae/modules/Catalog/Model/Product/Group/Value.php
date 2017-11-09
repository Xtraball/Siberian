<?php

class Catalog_Model_Product_Group_Value extends Catalog_Model_Product_Group {

    protected $_all_options;

    public function __construct($datas = array()) {
        parent::__construct($datas);
        $this->_db_table = 'Catalog_Model_Db_Table_Product_Group_Value';
    }

    public function save() {

        parent::save();

        if($this->getNewOptionValue() AND !$this->getIsDeleted()) {

//            // Supprime toutes les option_values décochées
//            $option_ids = array();
//            $options = $this->getOptions();
//            foreach($this->getNewOptionValue() as $option_datas) {
//                if(!empty($option_datas['option_id'])) $option_ids[] = $option_datas['option_id'];
//            }
//            foreach($options as $option) {
//                if(!in_array($option->getOptionId(), $option_ids)) $option->delete();
//            }

            // Sauvegarde toutes les option_values passées en post
            foreach($this->getNewOptionValue() as $option_id => $option_datas) {
                if(empty($option_datas['option_id']) AND empty($option_datas['is_deleted'])) continue;
                $option = new Catalog_Model_Product_Group_Option_Value();
                $option->find(array('group_value_id' => $this->getId(), 'option_id' => $option_id));
                if(!$option->getId()) {
                    $option_datas['group_value_id'] = $this->getId();
                }

                $option->setData($option_datas)->save();
            }
        }

    }

//    public function find($id, $field = null) {
//        parent::find($id, $field);
//        $this->_addGroupDatas();
//        return $this;
//    }

    public function findAllGroups($product_id = null, $app_id = null, $as_checkbox = false) {
        return $this->getTable()->findAllGroups($product_id, $app_id, $as_checkbox);
    }

    public function getAllOptions($product_id) {
        if(!$this->_all_options) {
            $option = new Catalog_Model_Product_Group_Option_Value();
            $this->_all_options = $option->findAllOptions($this->getGroupId(), $product_id);
        }
        return $this->_all_options;
    }

    public function getOptions() {

        if(!$this->_options) {
            $option = new Catalog_Model_Product_Group_Option_Value();
            $this->_options = $option->findAll(array('group_value_id' => $this->getId()));
        }

        return $this->_options;

    }

    public function toJson() {

        $options = array();
        $datas = array(
            'id' => $this->getGroupId(),
            'title' => $this->getTitle(),
            'is_required' => $this->isRequired()
        );
        foreach($this->getOptions() as $option) {
            $options[$option->getId()] = array(
                'id' => $option->getOptionId(),
                'name' => $option->getName(),
                'price' => $option->getPrice(),
                'formatted_price' => $option->getFormattedPrice(),
                'is_selected' => false
            );
        }
        $datas['options'] = $options;

        return Zend_Json::encode($datas);

    }

//    protected function _addGroupDatas() {
//
//        if($this->getGroupId()) {
//            $group = new Catalog_Model_Product_Group();
//            $group->find($this->getGroupId());
//            foreach($group->getData() as $key => $data) {
//                if(is_null($this->getData($key))) $this->setData($key, $value);
//            }
//        }
//
//    }

}