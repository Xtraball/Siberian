<?php

class Catalog_Model_Db_Table_Product_Group_Option_Value extends Core_Model_Db_Table {

    protected $_name = "catalog_product_group_option_value";
    protected $_primary = "value_id";

    public function findAll($values, $order, $params) {

        $where = array();
        $cols = array_keys($this->_db->describeTable('catalog_product_group_option'));
        $cols = array_combine($cols, $cols);
        unset($cols['option_id']);

        $select = $this->select()->setIntegrityCheck(false);
        $select->from(array('cpgov' => $this->_name))
            ->join(array('cpgo' => 'catalog_product_group_option'), 'cpgo.option_id = cpgov.option_id', $cols)
        ;

        if(!empty($values)) {
            foreach($values as $quote => $value) {
                if($quote instanceof Zend_Db_Expr) $where[] = $quote;
                else if(stripos($quote, '?') !== false) $where[] = $this->_db->quoteInto($quote, $value);
                else $where[] = $this->_db->quoteInto($quote . ' = ?', $value);
            }
        }
        if(!empty($where)) $select->where(join(' AND ', $where));

        if(!empty($params)) {
            $limit = !empty($params['limit']) ? $params['limit'] : null;
            $offset = !empty($params['offset']) ? $params['offset'] : null;
            $select->limit($limit, $offset);
        }

        return $this->fetchAll($select);
    }

    public function findAllOptions($group_id, $product_id) {

        $join = implode(' AND ', array(
            'cpgv.group_id = cpgo.group_id',
            $this->_db->quoteInto('cpgv.product_id = ?', $product_id)
        ));

        $fields = array_keys($this->_db->describeTable($this->_name));
        $fields = array_combine($fields, $fields);
        unset($fields['option_id']);

        $select = $this->select()
            ->from(array('cpgo' => 'catalog_product_group_option'))
            ->joinLeft(array('cpgv' => 'catalog_product_group_value'), $join, array())
            ->joinLeft(array('cpgov' => $this->_name), 'cpgov.group_value_id = cpgv.value_id AND cpgov.option_id = cpgo.option_id', $fields)
            ->columns(array('is_selected' => new Zend_Db_Expr('IF(cpgov.value_id IS NULL, 0, 1)')))
            ->where('cpgo.group_id = ?', $group_id)
            ->setIntegrityCheck(false)
        ;
//        Zend_Debug::dump($select->assemble()); die;
        return $this->fetchAll($select);

    }

}