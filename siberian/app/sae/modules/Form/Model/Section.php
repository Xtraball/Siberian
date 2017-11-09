<?php

class Form_Model_Section extends Core_Model_Default {

    protected $_fields = array();

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Form_Model_Db_Table_Section';
        return $this;
    }

    public function getFields() {

        if(!$this->_fields) {
            $field = new Form_Model_Field();
            $this->_fields = $field->findAll(array('section_id' => $this->getId()), 'position ASC');
        }

        return $this->_fields;
    }

    public function addField($field) {
        $this->_fields[] = $field;
        return $this;
    }

    public function setFields($fields) {
        $this->_fields = $fields;
        return $this;
    }

    public function findFields() {
        $field = new Form_Model_Field();
        $fields = $field->findAll(array('section_id' => $this->getId()));
        foreach($fields as $field) {
            $this->addField($field);
        }
        return $this;
    }

    public function findByValueId($value_id) {
        return $this->getTable()->findByValueId($value_id);
    }

}
