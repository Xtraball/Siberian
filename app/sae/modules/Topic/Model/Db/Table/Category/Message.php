<?php

class Topic_Model_Db_Table_Category_Message extends Core_Model_Db_Table {

    protected $_name    = "topic_category_message";
    protected $_primary = "category_message_id";

    public function findCategoryByMessageId($message_id) {
        $select = $this->select()
            ->from(array('pcm' => $this->_name))
            ->where('pcm.message_id = ?', $message_id)
        ;

        return $this->fetchAll($select);
    }
}