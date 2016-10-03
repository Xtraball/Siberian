<?php

class Job_Model_Job extends Core_Model_Default {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Job_Model_Db_Table_Job';
        return $this;
    }

    /**
     * Creates the root option used for options
     *
     * @param $option_value
     * @return $this
     */
    public function prepareFeature($option_value) {

        parent::prepareFeature($option_value);

        if (!$this->getId()) {
            $this->setValueId($option_value->getId())->save();
        }

        return $this;
    }
}