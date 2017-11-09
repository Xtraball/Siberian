<?php

class Form_Model_Field extends Core_Model_Default {

    protected static $_types = array();

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Form_Model_Db_Table_Field';
        return $this;
    }

    public function isRequired() {
        return $this->getData('required');
    }

    public function hasOptions() {
        return in_array($this->getType(), array("checkbox", "radio", "select"));
    }

    public function getOptions() {
        $options = array();
        foreach(explode(";", $this->getOption()) as $key => $value) {
            $options[] = array(
                "id" => $key,
                "name" => $value
            );
        }

        return $options;
    }

    public function getTypes() {

        if(empty(self::$_types)) {
            self::$_types = array(
                new Core_Model_Default(array('type' => 'texte', 'label' => $this->_('Text'), 'icon' => '<i class="icon-file-text-alt"></i>')),
                new Core_Model_Default(array('type' => 'textarea', 'label' => $this->_('Multiline text'), 'icon' => '<i class="icon-file-text"></i>')),
                new Core_Model_Default(array('type' => 'email', 'label' => $this->_('Email'), 'icon' => '@')),
                new Core_Model_Default(array('type' => 'nombre', 'label' => $this->_('Number'), 'icon' => '123')),
                new Core_Model_Default(array('type' => 'date', 'label' => $this->_('Date/Hour'), 'icon' => '<i class="icon-time"></i>')),
                new Core_Model_Default(array('type' => 'geoloc', 'label' => $this->_('Geolocation'), 'icon' => '<i class="icon-map-marker"></i>')),
                new Core_Model_Default(array('type' => 'checkbox', 'label' => $this->_('Checkbox'), 'icon' => '<i class="icon-check-sign"></i>')),
                new Core_Model_Default(array('type' => 'radio', 'label' => $this->_('Radio'), 'icon' => '<i class="icon-circle-blank"></i>')),
                new Core_Model_Default(array('type' => 'select', 'label' => $this->_('Drop down'), 'icon' => '<i class="icon-collapse"></i>')),
                new Core_Model_Default(array('type' => 'image', 'label' => $this->_('Image'), 'icon' => '<i class="icon-picture"></i>'))
            );
        }

        return self::$_types;

    }

    public function getTypeLabel() {

        foreach($this->getTypes() as $type) {
            if($type->getType() == $this->getType()) return $type->getLabel();
        }

        return $this->getType();

    }

    /**
     * Recherche par section_id
     *
     * @param int $section_id
     * @return object
     */
    public function findBySectionId($section_id) {
        return $this->getTable()->findBySectionId($section_id);
    }

    /**
     * Update la position des champs
     *
     * @param array $rows
     * @return object
     */
    public function updatePosition($rows) {
    	$this->getTable()->updatePosition($rows);
    	return $this;
    }
}
