<?php

class Mcommerce_Model_Db_Table_Delivery_Method extends Core_Model_Db_Table {

    protected $_name    = "mcommerce_delivery_method";
    protected $_primary = "method_id";

    public function findByStore($id) {

        $select = $this->select()
            ->from(array('mdm' => $this->_name))
            ->join(array('msdm' => 'mcommerce_store_delivery_method'), 'msdm.method_id = mdm.method_id', array('store_delivery_method_id', 'tax_id', 'price', 'min_amount_for_free_delivery'))
            ->where('msdm.store_id = ?', $id)
            ->setIntegrityCheck(false)
        ;

        return $this->fetchAll($select);

    }

    public function saveStoreDatas($store_id, $method_datas) {

        $this->_db->delete('mcommerce_store_delivery_method', array('store_id = ?' => $store_id));
        $fields = array_keys($this->_db->describeTable('mcommerce_store_delivery_method'));
        $fields = array_combine($fields, $fields);

        foreach($method_datas as $method_data) {

            if(empty($method_data['method_id'])) continue;
            $datas = array_intersect_key($method_data, $fields);
            $datas['store_id'] = $store_id;

            $this->_db->insert('mcommerce_store_delivery_method', $datas);
        }

        return $this;
    }

}
