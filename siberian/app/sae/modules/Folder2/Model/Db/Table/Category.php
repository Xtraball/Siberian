<?php

/**
 * Class Folder2_Model_Db_Table_Category
 */
class Folder2_Model_Db_Table_Category extends Core_Model_Db_Table {

    /**
     * @var string
     */
    protected $_name = 'folder_category';

    /**
     * @var string
     */
    protected $_primary = 'category_id';

    /**
     * @param $parentId
     * @return mixed
     */
    public function findRootCategoryId($parentId) {
        $cpt = 0;
        $lastParentId = $parentId;
        while(!is_null($parentId) AND ++$cpt < 10) {
            $lastParentId = $parentId;
            $select = $this->_db->select()
                ->from($this->_name, ['parent_id'])
                ->where('category_id = ?', $parentId);
            $parentId = $this->_db->fetchOne($select);
        }

        return $lastParentId;
    }

    /**
     * @param $parentId
     * @return int|string
     */
    public function getLastCategoryPosition($parentId) {

        $select = $this->select()->from($this->_name, ['pos'])
            ->where('parent_id = ?', $parentId)
            ->order('pos DESC')
            ->limit(1);

        $position = $this->_db->fetchOne($select);

        return $position ? $position : 0;
    }
}