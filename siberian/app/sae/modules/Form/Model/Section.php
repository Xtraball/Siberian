<?php

/**
 * Class Form_Model_Section
 */
class Form_Model_Section extends Core_Model_Default
{

    /**
     * @var array
     */
    protected $_fields = [];

    /**
     * Form_Model_Section constructor.
     * @param array $params
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = 'Form_Model_Db_Table_Section';
        return $this;
    }

    /**
     * @return array
     */
    public function getFields()
    {

        if (!$this->_fields) {
            $field = new Form_Model_Field();
            $this->_fields = $field->findAll(['section_id' => $this->getId()], 'position ASC');
        }

        return $this->_fields;
    }

    /**
     * @param $field
     * @return $this
     */
    public function addField($field)
    {
        $this->_fields[] = $field;
        return $this;
    }

    /**
     * @param $fields
     * @return $this
     */
    public function setFields($fields)
    {
        $this->_fields = $fields;
        return $this;
    }

    /**
     * @return $this
     */
    public function findFields()
    {
        $field = new Form_Model_Field();
        $fields = $field->findAll(['section_id' => $this->getId()]);
        foreach ($fields as $field) {
            $this->addField($field);
        }
        return $this;
    }

    /**
     * @param $value_id
     * @return mixed
     */
    public function findByValueId($value_id)
    {
        return $this->getTable()->findByValueId($value_id);
    }

}
