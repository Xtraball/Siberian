<?php

class Topic_Model_Db_Table_Category extends Core_Model_Db_Table {

    protected $_name    = "topic_category";
    protected $_primary = "category_id";

    public function getTopicCategories($topic_id, $all = false) {
        $select = $this->select()
            ->from(array('pc' => $this->_name))
            ->order("pc.position ASC")
            ->where('pc.topic_id = ?', $topic_id);

        if($all == false)
            $select = $select->where('pc.parent_id is null');

        return $this->fetchAll($select);
    }

    public function getChildren($category_id) {
        $select = $this->select()
            ->from(array('pc' => $this->_name))
            ->where('pc.parent_id = ?', $category_id)
            ->order("pc.position ASC")
        ;

        return $this->fetchAll($select);
    }

    public function deleteByParentId($parent_id) {
        $this->delete(array("parent_id = ?" => $parent_id));
        return $this;
    }

    public function getMaxPosition($topic_id) {
        $select = $this->_db->select()
            ->from(array('pc' => $this->_name),array("max" => "MAX(position)"))
            ->where("topic_id = ?",$topic_id)
        ;

        return $this->_db->fetchOne($select);
    }

}