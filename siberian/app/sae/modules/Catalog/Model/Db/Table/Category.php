<?php

class Catalog_Model_Db_Table_Category extends Core_Model_Db_Table
{
    protected $_name = "catalog_category";
    protected $_primary = "category_id";

    public function findByValueId($value_id, $pos_id, $only_active, $only_first_level) {

        $select = $this->select()
            ->from(array('cc' => $this->_name))
            ->where('cc.value_id = ?', $value_id)
        ;

        if($only_active) $select->where('cc.is_active = 1');
        if($only_first_level) $select->where('cc.parent_id IS NULL');

        $select->order('position ASC');

        return $this->fetchAll($select);
    }

    public function updatePosition($ids) {
    	foreach($ids as $pos => $id) {
    		$this->_db->update($this->_name, array('position' => $pos), array('category_id = ?' => $id));
    	}

    	return $this;
    }

    public function findLastPosition($value_id, $parent_id) {
        $select = $this->select()
            ->from($this->_name, array('position'))
            ->where('value_id = ?', $value_id)
            ->order('position DESC')
            ->limit(1)
        ;

        if($parent_id) $select->where('parent_id = ?', $parent_id);
        else $select->where('parent_id IS NULL');

        $pos = $this->_db->fetchOne($select);

        return !empty($pos) ? $pos : 0;

    }

    public function getPosIds($category_id) {
        return $this->_db->fetchCol($this->_db->select()->from('catalog_category_pos', 'pos_id')->where('category_id = ?', $category_id));
    }

    public function updateOutlets($category_id, array $pos_ids = array()) {

        $this->_db->delete('catalog_category_pos', $this->_db->quoteInto('category_id = ?', $category_id));
        foreach($pos_ids as $pos_id) {
            $this->_db->insert('catalog_category_pos', array('category_id' => $category_id, 'pos_id' => $pos_id));
        }

        return $this;

    }
}