<?php

class Cms_Model_Application_Page_Block_Video extends Cms_Model_Application_Page_Block_Abstract
{

    protected $_types = array(
        1 => 'link',
        2 => 'youtube',
        3 => 'podcast',
    );

    protected $_type_instance;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Cms_Model_Db_Table_Application_Page_Block_Video';
        return $this;
    }

    public function find($id, $field = null) {
        parent::find($id, $field);
        $this->_addTypeDatas();
        return $this;
    }

    public function isValid() {
        return $this->getTypeInstance() ? $this->getTypeInstance()->isValid() : false;
    }

    public function getImageUrl() {
        if($this->isValid()) return $this->getTypeInstance()->getImageUrl();
        return '';
    }

    public function getTypeInstance() {
        if(!$this->_type_instance) {
            $type = $this->getTypeId();
            if(in_array($type, $this->_types)) {
                $class = 'Cms_Model_Application_Page_Block_Video_'.ucfirst($type);
                $this->_type_instance = new $class();
                $this->_type_instance->find($this->getId());
                $this->_type_instance->addData($this->getData());
            }
        }

        return !empty($this->_type_instance) ? $this->_type_instance : null;

    }

    public function getList($search) {
        return $this->getTypeInstance()->getList($search);
    }

    public function save() {
        parent::save();
        if(!$this->getIsDeleted()) {
            if($this->getTypeInstance()->getId()) $this->getTypeInstance()->delete();
            $this->getTypeInstance()->setData($this->_getTypeInstanceData())->setVideoId($this->getId())->save();
        }

        return $this;
    }

    protected function _addTypeDatas() {
        if($this->getTypeInstance() AND $this->getTypeInstance()->getId()) {
            $this->addData($this->getTypeInstance()->getData());
        }

        return $this;
    }

    protected function _getTypeInstanceData() {
        $fields = $this->getTypeInstance()->getFields();
        $datas = array();
        foreach($fields as $field) {
            $datas[$field] = $this->getData($field);
        }

        return $datas;
    }

}