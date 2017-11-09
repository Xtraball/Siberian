<?php

class Tour_Model_Db_Table_Step extends Core_Model_Db_Table {

    protected $_name    = "tour_step";
    protected $_primary = "tour_step_id";

    public function findAllForJS($language_code, $url) {
        $select = $this->select()
            ->from(array('ts' => $this->_name))
            ->where('ts.title is not null')
            ->where('ts.language_code = ?', $language_code)
            ->where('ts.url = ?', $url)
            ->order("ts.order_index ASC")
        ;

        return $this->fetchAll($select);
    }
}