<?php

class Folder_Model_Db_Table_Category extends Core_Model_Db_Table
{
    protected $_name = "folder_category";
    protected $_primary = "category_id";

    public function findRootCategoryId($parent_id) {

        $cpt = 0;
        $last_parent_id = $parent_id;
        while(!is_null($parent_id) AND ++$cpt < 10) {
            $last_parent_id = $parent_id;
            $select = $this->_db->select()->from($this->_name, array('parent_id'))->where('category_id = ?', $parent_id);
            $parent_id = $this->_db->fetchOne($select);
        }

        return $last_parent_id;

    }

    public function getLastCategoryPosition($parent_id) {

        $select = $this->select()->from($this->_name, array('pos'))
            ->where('parent_id = ?', $parent_id)
            ->order('pos DESC')
            ->limit(1)
        ;

        $position = $this->_db->fetchOne($select);

        return $position ? $position : 0;

    }

    public function getCategoryChildren($category_id) {

        $select = $this->select()->from($this->_name, array('category_id'))
            ->where('parent_id = ?', $category_id)
        ;

        $children = $this->_db->fetchAll($select);
        return $children;

    }

}