<?php

class Application_Model_Db_Table_Option extends Core_Model_Db_Table
{

    protected $_name = "application_option";
    protected $_primary = "option_id";

    public function findAll($values, $order, $params) {
        $rows = parent::findAll($values, $order, $params);

        foreach($rows as $row) {
            $row->prepareUri();
        }

        return $rows;
    }

}