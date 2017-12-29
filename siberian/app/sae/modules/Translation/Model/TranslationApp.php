<?php

/**
 * Class Translation_Model_TranslationApp
 *
 * @method $this setFilename(string $fileName)
 * @method $this setOrigin(string $origin)
 * @method $this setTarget(string $langId)
 * @method $this setKey(string $key)
 * @method $this setValue(string $value)
 */
class Translation_Model_TranslationApp extends Core_Model_Default {

    /**
     * Translation_Model_TranslationApp constructor.
     * @param array $params
     */
    public function __construct($params = []) {
        parent::__construct($params);
        $this->_db_table = 'Translation_Model_Db_Table_TranslationApp';
        return $this;
    }
}
