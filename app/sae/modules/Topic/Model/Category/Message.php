<?php

class Topic_Model_Category_Message extends Core_Model_Default {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Topic_Model_Db_Table_Category_Message';
        return $this;
    }

    public function findCategoryByMessageId($message_id) {
        $categories = $this->getTable()->findCategoryByMessageId($message_id);
        $data = array();
        foreach($categories as $category) {
            $data[] = $category->getCategoryId();
        }

        return $data;
    }

}
