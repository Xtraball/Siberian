<?php

class Cms_Model_Application_Block extends Core_Model_Default {

    protected $_object;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Cms_Model_Db_Table_Application_Block';
        return $this;
    }

    public function findByPage($page_id) {
        return $this->getTable()->findByPage($page_id);
    }

    public function getObject() {
        if(!$this->_object AND $this->getType()) {
            $type = $this->getType() ? ucfirst($this->getType()) : 'Text';
            $class = 'Cms_Model_Application_Page_Block_'.$type;
            $this->_object = new $class();
            $this->_object->find($this->getValueId(), 'value_id');
        }

        return $this->_object;
    }

    public function getImageUrl() {
        return $this->getObject() ? $this->getObject()->getImageUrl() : '';
    }

}