<?php

class Application_Model_Option_Category extends Core_Model_Default {

    protected $_options;

    public function __construct($datas = array()) {
        parent::__construct($datas);
        $this->_db_table = 'Application_Model_Db_Table_Option_Category';
    }

    public function getOptions() {

        if(!$this->_options) {

            $option = new Application_Model_Option();
            $this->_options = $option->findAll(array("category_id = ?" => $this->getId()));

        }

        return $this->_options;

    }

    public function getName() {
        return __($this->getData("name"));
    }
    
}
