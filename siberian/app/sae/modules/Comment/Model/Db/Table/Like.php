<?php

class Comment_Model_Db_Table_Like extends Core_Model_Db_Table
{
    protected $_name = "comment_like";
    protected $_primary = "like_id";
    
    public function findByComment($comment_id, $pos_id) {

        $where = array($this->_db->quoteInto('comment_id = ?', $comment_id));
        
        if($pos_id) {
            $where[] = $this->_db->quoteInto('pos_id = ?', $pos_id);
        }

        $where = join(' AND ', $where);
        return $this->fetchAll($where);
    }
    
    public function findByIp($comment_id, $customer_id, $ip, $ua) {

        $where = array($this->_db->quoteInto('comment_id = ?', $comment_id));
        if(!empty($customer_id)) {
            $where[] = $this->_db->quoteInto('customer_id = ?', $customer_id);
        } else {
            $where[] = $this->_db->quoteInto('customer_ip = ?', $ip);
            $where[] = $this->_db->quoteInto('user_agent = ?', $ua);
        }

        $where = join(' AND ', $where);
        return $this->fetchAll($where);
    }

}