<?php

class Mcommerce_Model_Db_Table_Tax extends Core_Model_Db_Table
{
    protected $_name = "mcommerce_tax";
    protected $_primary = "tax_id";

    public function findByStore($store_id) {

        $join = implode(' AND ', [
            'mst.tax_id = mt.tax_id',
            $this->_db->quoteInto('mst.store_id = ?', $store_id)
        ]);

        $select = $this->select()
            ->from(['mt' => $this->_name])
            ->join(['mst' => 'mcommerce_store_tax'], $join, ['rate'])
            ->setIntegrityCheck(false)
        ;
//        Zend_Debug::dump($select->assemble());
//        die;
        return $this->fetchAll($select);
    }

    public function saveStoreTaxes($tax_id, $datas) {

        $this->_db->delete('mcommerce_store_tax', ['tax_id = ?' => $tax_id]);
        foreach($datas as $store_id => $rate) {
            $this->_db->insert('mcommerce_store_tax', ['store_id' => $store_id, 'tax_id' => $tax_id, 'rate' => $rate]);
        }

        return $this;

    }
}