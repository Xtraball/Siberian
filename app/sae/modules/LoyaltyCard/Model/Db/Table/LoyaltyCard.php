<?php

class LoyaltyCard_Model_Db_Table_LoyaltyCard extends Core_Model_Db_Table
{
    protected $_name = "loyalty_card";
    protected $_primary = "card_id";

    public function findByValueId($id) {
        return $this->fetchAll($this->_db->quoteInto('value_id = ?', $id));
    }

    public function findLast($value_id) {

        $select = $this->select()->setIntegrityCheck(false)
            ->from(array('fc' => $this->_name))
            ->joinLeft(array('fcr' => 'loyalty_card_pos'), 'fcr.card_id = fc.card_id', array())
            ->where('fc.value_id = ?', $value_id)
            ->order('card_id DESC')
        ;

        $rows = array();
        $rows_tmp = array();
        foreach($this->_fetch($select) as $row) {
            $rows_tmp[] = $row;
        }

        foreach($rows_tmp as $row) {
            if(empty($rows[$row['card_id']])) $rows[$row['card_id']] = $row;
        }

        $data  = array(
            'table'    => $this,
            'data'     => array_values($rows),
            'readOnly' => $select->isReadOnly(),
            'rowClass' => $this->getRowClass(),
            'stored'   => true
        );

        $rowsetClass = $this->getRowsetClass();
        if (!class_exists($rowsetClass)) {
            require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($rowsetClass);
        }

        return new $rowsetClass($data);

    }

}