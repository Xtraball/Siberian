<?php
class Comment_Model_Db_Table_Comment extends Core_Model_Db_Table {

    protected $_name="comment";
    protected $_primary="comment_id";

    public function findByPos($value_id) {

        $select = $this->_prepareSelect($value_id);
        $select->order('created_at DESC');

        return $this->fetchAll($select);
    }

    public function findLast($value_id) {
        $select = $this->_prepareSelect($value_id);
        $select
            ->where('is_visible = 1')
            ->order('c.created_at DESC')
            ->limit(1)
        ;

        return $this->fetchRow($select);
    }

    public function findLastest($value_id) {
        $select = $this->_prepareSelect($value_id);
        $select
            ->where('is_visible = 1')
            ->order('c.created_at DESC')
            ->limit(10)
        ;
        return $this->fetchAll($select);
    }

    public function findAllWithPhoto($value_id) {
        $select = $this->_prepareSelect($value_id);
        $select
            ->where('is_visible = 1')
            ->where('image IS NOT NULL')
            ->order('c.created_at DESC')
            ->limit(10)
        ;
        return $this->fetchAll($select);
    }

    public function findAllWithLocation($value_id, $offset) {
        $select = $this->_prepareSelect($value_id);
        $select
            ->where('is_visible = 1')
            ->where('latitude IS NOT NULL')
            ->where('longitude IS NOT NULL')
            ->order('c.created_at DESC')
            ->limit(Comment_Model_Comment::DISPLAYED_PER_PAGE, $offset)
        ;
        return $this->fetchAll($select);
    }

    public function findAllWithLocationAndPhoto($value_id) {
        $select = $this->_prepareSelect($value_id);
        $select
            ->where('is_visible = 1')
            ->where('image IS NOT NULL')
            ->where('latitude IS NOT NULL')
            ->where('longitude IS NOT NULL')
            ->order('c.created_at DESC')
            ->limit(10)
        ;
        return $this->fetchAll($select);
    }

    public function pullMore($value_id, $start, $count) {
        $select = $this->_prepareSelect($value_id);
        $select
            ->where('is_visible = 1')
            ->where('comment_id < ?', $start)
            ->order('c.created_at DESC')
            ->limit($count)
        ;
        return $this->fetchAll($select);
    }

    protected function _prepareSelect($value_id) {

        $select = $this->select()
            ->from(array('c' => $this->_name))
            ->where($this->_db->quoteInto('c.value_id = ?', $value_id))
        ;

        return $select;

    }

}