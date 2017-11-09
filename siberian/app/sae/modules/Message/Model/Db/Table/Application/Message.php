<?php

class Message_Model_Db_Table_Application_Message extends Core_Model_Db_Table {

    protected $_name = "message_application";
    protected $_primary = "message_id";

    public function findAllByAppId($app_id, $offset) {
        $select = $this->select()
            ->from(array('me' => $this->_name))
            ->joinLeft(array('a' => 'admin'), 'me.author_id = a.admin_id', array("a.firstname", "a.lastname"))
            ->where('me.app_id = ?', $app_id)
            ->order('me.message_id DESC')
            ->limit(Message_Model_Application_Message::DISPLAY_PER_PAGE,$offset)
            ->setIntegrityCheck(false)
        ;

        return $this->fetchAll($select);
    }
}