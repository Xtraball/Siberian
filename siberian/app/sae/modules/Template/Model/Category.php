<?php

class Template_Model_Category extends Core_Model_Default {

    protected $_blocks;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Template_Model_Db_Table_Category';
        return $this;
    }

}
