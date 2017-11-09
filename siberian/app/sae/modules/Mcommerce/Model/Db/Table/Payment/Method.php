<?php

class Mcommerce_Model_Db_Table_Payment_Method extends Core_Model_Db_Table {

    protected $_name    = "mcommerce_payment_method";
    protected $_primary = "method_id";

    public function findByStore($id) {

        $select = $this->select()
            ->from(array('mpm' => $this->_name))
            ->join(array('mspm' => 'mcommerce_store_payment_method'), 'mspm.method_id = mpm.method_id', array('store_payment_method_id', 'store_id'))
            ->where('mspm.store_id = ?', $id)
            ->setIntegrityCheck(false)
        ;

        return $this->fetchAll($select);

    }

    public function saveStoreDatas($store_id, $method_datas) {

        $this->_db->delete('mcommerce_store_payment_method', array('store_id = ?' => $store_id));
        $fields = array_keys($this->_db->describeTable('mcommerce_store_payment_method'));
        $fields = array_combine($fields, $fields);

        foreach($method_datas as $method_data) {

            if(empty($method_data['method_id'])) continue;
            $datas = array_intersect_key($method_data, $fields);
            $datas['store_id'] = $store_id;

            $this->_db->insert('mcommerce_store_payment_method', $datas);
        }

        return $this;

    }

}
