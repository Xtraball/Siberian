<?php

/**
 * Class Application_Model_Db_Table_Option
 */
class Application_Model_Db_Table_Option extends Core_Model_Db_Table
{

    /**
     * @var string
     */
    protected $_name = "application_option";
    /**
     * @var string
     */
    protected $_primary = "option_id";

    /**
     * @param $values
     * @param null $order
     * @param array $params
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function findAll($values, $order = null, $params = [])
    {
        $rows = parent::findAll($values, $order, $params);

        foreach ($rows as $row) {
            $row->prepareUri();
        }

        return $rows;
    }

}