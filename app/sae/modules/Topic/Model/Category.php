<?php

class Topic_Model_Category extends Core_Model_Default {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Topic_Model_Db_Table_Category';
        return $this;
    }

    public function delete() {
        $this->getTable()->deleteByParentId($this->getId());
        parent::delete();
        return $this;
    }

    public function getTopicCategories($topic_id) {
        return $this->getTable()->getTopicCategories($topic_id);
    }

    public function getChildren() {
        return $this->getTable()->getChildren($this->getId());
    }

    public function getMaxPosition($topic_id) {
        return $this->getTable()->getMaxPosition($topic_id);
    }
}
