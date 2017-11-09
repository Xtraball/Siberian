<?php

class Api_Model_Provider extends Core_Model_Default {

    protected $_keys;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Api_Model_Db_Table_Provider';
        return $this;
    }

    public function getKeys() {

        if(!$this->_keys) {
            $key = new Api_Model_Key();
            $this->_keys = $key->findAll(array('provider_id' => $this->getId()));
        }

        return $this->_keys;

    }

}
