<?php

class Comment_Model_Answer extends Core_Model_Default
{

    protected $_customer;
    protected $_comment;

    public function __construct($datas = array()) {
        parent::__construct($datas);
        $this->_db_table = 'Comment_Model_Db_Table_Answer';
    }

    public function findByComment($comment_id, $viewAll = false, $pos_id = null) {
        $viewAll = true;
        return $this->getTable()->findByComment($comment_id, $viewAll, $pos_id);
    }

    public function setComment($comment) {
        $this->_comment = $comment;
        return $this;
    }

    public function getComment() {
        if(is_null($this->_comment)) {
            $comment = new Comment_Model_Comment();
            $comment->find($this->getCommentId());
            $this->_comment = $comment;
        }
        return $this->_comment;
    }

    public function getCustomer() {
        if(is_null($this->_customer)) {
            $customer = new Customer_Model_Customer();
            $this->_customer = $customer->find($this->getCustomerId());
        }

        return $this->_customer;
    }

    public function isVisible() {
        return $this->getData('is_visible');
    }

    public function getFormattedCreatedAtDate() {
        $date = new Zend_Date($this->getCreatedAt());
        return $date->toString('dd/MM/y');
    }

    public function getFormattedCreatedAtTime() {
        $date = new Zend_Date($this->getCreatedAt());
        return $date->toString('HH').'h'.$date->toString('mm');
    }

    public function save() {

        if(!$this->getSkipIsVisibleState()) {
//            $config = new Comment_Model_Pos_Config();
//            $config->findByPosId($this->getPosId());
//            $this->setIsVisible((int) $config->isAutocommit());
//            Zend_Debug::dump($config->getData());
//            Zend_Debug::dump($this->getData());
//            die;
        }
        $this->setIsVisible(1);
        return parent::save();
    }
}