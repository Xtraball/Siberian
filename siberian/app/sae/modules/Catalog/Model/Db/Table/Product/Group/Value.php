<?php

class Catalog_Model_Db_Table_Product_Group_Value extends Core_Model_Db_Table
{
    protected $_name = "catalog_product_group_value";
    protected $_primary = "value_id";

    public function findAll($values, $order, $params) {

        $where = array();
        $cols = array_keys($this->_db->describeTable('catalog_product_group'));
        $cols = array_combine($cols, $cols);
        unset($cols['group_id']);

        $select = $this->select()->setIntegrityCheck(false);
        $select->from(array('cpgv' => $this->_name))
            ->join(array('cpg' => 'catalog_product_group'), 'cpg.group_id = cpgv.group_id', $cols)
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

    public function findAllGroups($product_id = null, $app_id = null, $as_checkbox = false) {

        $join = implode(' AND ', array(
            'cpgv.group_id = cpg.group_id',
            $this->_db->quoteInto('cpgv.product_id = ?', $product_id)
        ));

        $fields = array_keys($this->_db->describeTable($this->_name));
        $fields = array_combine($fields, $fields);
        unset($fields['group_id']);
        $fields['is_selected'] = new Zend_Db_Expr('IF(cpgv.value_id IS NULL, 0, 1)');

        $select = $this->select()
            ->from(array('cpg' => 'catalog_product_group'))
            ->joinLeft(array('cpgv' => $this->_name), $join, $fields)
            ->setIntegrityCheck(false)
        ;

        if($app_id) {
            $select->where("cpg.app_id = ?", $app_id);
        }
        $select->where("cpg.as_checkbox = ?", $as_checkbox);

        return $this->fetchAll($select);

    }

}