<?php
class Radio_Model_Radio extends Core_Model_Default {

    protected $_is_cachable = false;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Radio_Model_Db_Table_Radio';
        return $this;
    }

    public function copyTo($option) {
        $this->setId(null)->setValueId($option->getId())->save();
        return $this;
    }

}
