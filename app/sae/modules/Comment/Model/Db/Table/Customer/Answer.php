<?php

class Comment_Model_Db_Table_Customer_Answer extends Core_Model_Db_Table
{
    protected $_name = "comment_answer";
    protected $_primary = "answer_id";

    public function findByStatus($status_id, $viewAll, $pos_id) {

        $where = array($this->_db->quoteInto('comment_id = ?', $status_id));
        if(!$viewAll) {
            $where[] = 'is_visible = 1';
        }
        if($pos_id) {
            $where[] = $this->_db->quoteInto('pos_id = ?', $pos_id);
        }

        $where = join(' AND ', $where);
        return $this->fetchAll($where);
    }
}