<?php

/**
 * Class Api_Model_Key
 *
 * @method string getKey()
 * @method string getValue()
 */
class Api_Model_Key extends Core_Model_Default {

    /**
     * @var array
     */
    private static $__keys = [];

    /**
     * Api_Model_Key constructor.
     * @param array $params
     */
    public function __construct($params = []) {
        parent::__construct($params);
        $this->_db_table = 'Api_Model_Db_Table_Key';
        return $this;
    }

    /**
     * @param $providerCode
     * @return Api_Model_Key|mixed
     */
    public static function findKeysFor($providerCode) {
        if (empty(self::$__keys[$providerCode])) {
            $key = new self();
            $provider = (new Api_Model_Provider())
                ->find($providerCode, 'code');

            if (!$provider->getId()) {
                return $key;
            }

            foreach ($provider->getKeys() as $tmpKey) {
                $key->addData($tmpKey->getKey(), $tmpKey->getValue());
            }

            self::$__keys[$providerCode] = $key;
        }

        return self::$__keys[$providerCode];
    }

}
