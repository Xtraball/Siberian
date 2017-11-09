<?php
class Comment_Model_Like extends Core_Model_Default {

    protected $_comment;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Comment_Model_Db_Table_Like';
        return $this;
    }
    
    public function findByComment($comment_id, $pos_id = null) {
        $viewAll = true;
        return $this->getTable()->findByComment($comment_id, $pos_id);
    }
    
    public function setComment($comment) {
        $this->_comment = $comment;
        return $this;
    }

    public function findByIp($comment_id, $customer_id, $ip, $ua) {
        $like = $this->getTable()->findByIp($comment_id, $customer_id, $ip, $ua);
        return $like->count() > 0;
    }

}
