<?php
class Api_Model_Key extends Core_Model_Default {

    private static $__keys = array();

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Api_Model_Db_Table_Key';
        return $this;
    }

    public static function findKeysFor($provider_code) {

        if(empty(self::$__keys[$provider_code])) {
            $key = new self();
            $provider = new Api_Model_Provider();
            $provider->find($provider_code, 'code');

            if(!$provider->getId()) return $key;

            foreach($provider->getKeys() as $tmp_key) {
                $key->addData($tmp_key->getKey(), $tmp_key->getValue());
            }

            self::$__keys[$provider_code] = $key;
        }

        return self::$__keys[$provider_code];
    }

}
