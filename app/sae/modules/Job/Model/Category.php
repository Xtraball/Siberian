<?php

class Job_Model_Category extends Core_Model_Default {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Job_Model_Db_Table_Category';
        return $this;
    }

    /**
     * @return mixed
     */
    public function toggle() {
        $this->setIsActive(!$this->getIsActive())->save();

        return $this->getIsActive();
    }
}