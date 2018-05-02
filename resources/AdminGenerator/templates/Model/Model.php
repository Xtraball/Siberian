<?php

/**
 * Class #MODULE#_Model_#MODEL#
 */
class #MODULE#_Model_#MODEL# extends Core_Model_Default
{
    /**
     *  constructor.
     * @param array $params
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = '#MODULE#_Model_Db_Table_#MODEL#';
        return $this;
    }
}