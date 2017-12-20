<?php

/**
 * Class Api_Model_Provider
 *
 * @method integer getId()
 */
class Api_Model_Provider extends Core_Model_Default {

    /**
     * @var Api_Model_Key[]
     */
    protected $_keys;

    /**
     * Api_Model_Provider constructor.
     * @param array $params
     */
    public function __construct($params = []) {
        parent::__construct($params);
        $this->_db_table = 'Api_Model_Db_Table_Provider';
        return $this;
    }

    /**
     * @return Api_Model_Key[]
     */
    public function getKeys() {
        if (!$this->_keys) {
            $this->_keys = (new Api_Model_Key())->findAll([
                'provider_id' => $this->getId()
            ]);
        }

        return $this->_keys;
    }
}
