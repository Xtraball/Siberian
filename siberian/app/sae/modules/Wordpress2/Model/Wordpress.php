<?php

/**
 * Class Wordpress2_Model_Wordpress
 */
class Wordpress2_Model_Wordpress extends Core_Model_Default {

    /**
     * Wordpress2_Model_Wordpress constructor.
     * @param array $params
     */
    public function __construct($params = []) {
        parent::__construct($params);
        $this->_db_table = 'Wordpress2_Model_Db_Table_Wordpress';
        return $this;
    }
}