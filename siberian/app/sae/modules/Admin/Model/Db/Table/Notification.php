<?php

class Admin_Model_Db_Table_Notification extends Core_Model_Db_Table
{

    protected $_name = "notification";
    protected $_primary = "notification_id";

    public function findLastId() {
        $select = $this->_db->select()
            ->from($this->_name, array('tiger_notification_id'))
            ->order('tiger_notification_id DESC')
            ->limit(1)
        ;

        $last_id = $this->_db->fetchOne($select);

        return !$last_id ? 0 : $last_id;
    }

    public function countUnread() {
        $select = $this->_db->select()
            ->from($this->_name, array('unread' => new Zend_Db_Expr('COUNT(notification_id)')))
            ->where('is_read = 0')
        ;

        $nbr_unread = $this->_db->fetchOne($select);
        return !$nbr_unread ? 0 : $nbr_unread;
    }
}