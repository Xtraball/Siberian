<?php

class Backoffice_Model_Db_Table_Notification extends Core_Model_Db_Table {

    /**
     * @var string
     */
    protected $_name = "backoffice_notification";
    protected $_primary = "notification_id";

    /**
     * @return int|string
     */
    public function findLastId() {
        $select = $this->_db->select()
            ->from($this->_name, array('original_notification_id'))
            ->order('original_notification_id DESC')
            ->limit(1)
        ;

        $last_id = $this->_db->fetchOne($select);

        return !$last_id ? 0 : $last_id;
    }

    /**
     * @return int|string
     */
    public function countUnread() {
        $select = $this->_db->select()
            ->from($this->_name, array('unread' => new Zend_Db_Expr('COUNT(notification_id)')))
            ->where('is_read = 0')
        ;

        $nbr_unread = $this->_db->fetchOne($select);
        return !$nbr_unread ? 0 : $nbr_unread;
    }

    /**
     * Mark all as read.
     */
    public function markRead() {
        $this->_db->update(
            $this->_name,
            array("is_read" => 1)
        );
    }
}