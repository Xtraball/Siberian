<?php

class Topic_Model_Subscription extends Core_Model_Default {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Topic_Model_Db_Table_Subscription';
        return $this;
    }

    public function isSubscribed($category_id, $device_uid) {
        $is = $this->getTable()->isSubscribed($category_id,$device_uid);
        return count($is)==0?false:true;
    }

    public function findAllowedCategories($device_uid) {
        $categories = $this->getTable()->findAllowedCategories($device_uid);
        $data = array();
        foreach($categories as $category) {
            $data[] = $category->getCategoryId();
        }

        return $data;
    }

}
