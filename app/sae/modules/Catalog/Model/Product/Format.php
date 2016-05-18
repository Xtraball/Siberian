<?php

class Catalog_Model_Product_Format extends Catalog_Model_Product_Abstract
{
    protected $_options = array();
    protected $_options_by_pos = array();

    public function addOption($option) {

        if(is_array($option)) {
            $option = $this->newOption($option);
        }

        $this->_options[] = $option;
        return $this;
    }

    public function setOptions($options) {
        $this->_options = array();

        foreach($options as $id => $option) {
//            if(!empty($option['option_id'])) $id = intval($option['option_id']);
            $this->addOption($option);
        }
        return $this;
    }

    public function getOptions($pos_id = null) {
        if($pos_id) {
            if(empty($this->_options_by_pos[$pos_id])) {
                if($this->getProduct()->getId() AND $this->getProduct()->getProductId()) $this->loadOptions($pos_id);
            }
            return $this->_options_by_pos[$pos_id];
        }
        else {
            if(empty($this->_options)) {
                if($this->getProduct()->getId() AND $this->getProduct()->getProductId()) $this->loadOptions();
            }
            return $this->_options;
        }

        return array();
    }

    public function loadOptions($pos_id = null) {
        $option = new Catalog_Model_Product_Format_Option();
        $options = $option->findByProductId($this->getProduct()->getId(), $pos_id);
        foreach($options as $option) {
            if($pos_id) $this->_options_by_pos[$pos_id][$option->getId()] = $option;
            else $this->_options[] = $option;
        }

        return $this;
    }

    public function newOption($datas) {
        $option = new Catalog_Model_Product_Format_Option();
        $option->setData($datas)
            ->setProductId($this->getProduct()->getId())
            ->setId(!empty($datas['option_id']) ? $datas['option_id'] : null)
        ;

//        if(!empty($datas['pos'])) $option->setIsShared(1)->setPosDatas($datas['pos']);

        return $option;
    }

    public function save() {

        // Si le produit est supprimé, on ne sauvegarde pas
        if($this->getProduct()->getData('is_deleted') == 1) return $this;

        // Sauvegarde les nouvelles options du produit
        foreach($this->_options as $option) {
            if(!$option->getId()) $option->setProductId($this->getProduct()->getId());
//            Zend_Debug::dump($option->getData());
            $option->save();
        }
//        die;
    }
}