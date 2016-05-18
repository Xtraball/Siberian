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

//    public function savePageDatas($option_id, $datas) {
//        $this->_db->delete($this->_name.'_page', array('option_id = ?' => $option_id));
//        $this->_db->insert($this->_name.'_page', $datas);
//    }
//
//    public function addPageDatas($row) {
//        $select = $this->_db->select()
//            ->from(array('ao' => $this->_name), array())
//            ->join(array('aop' => $this->_name.'_page'), 'aop.option_id = ao.option_id')
//            ->where('ao.option_id = ?', $row->getId())
//        ;
//        $datas = $this->_db->fetchRow($select);
//        if($datas) {
//            $row->addData($datas);
//        }
//
//        return $this;
//    }
}