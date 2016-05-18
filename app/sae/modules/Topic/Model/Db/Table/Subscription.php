<?php

class Topic_Model_Db_Table_Subscription extends Core_Model_Db_Table {

    protected $_name    = "topic_subscription";
    protected $_primary = "subscription_id";

    public function isSubscribed($category_id,$device_uid) {
        $select = $this->select()
            ->from(array('pcs' => $this->_name))
            ->where('pcs.category_id = ?', $category_id)
            ->where('pcs.device_uid = ?', $device_uid)
        ;

        return $this->fetchAll($select);
    }

    public function findAllowedCategories($device_uid) {
        $select = $this->select()
            ->from(array('pcs' => $this->_name))
            ->where('pcs.device_uid = ?', $device_uid)
        ;
        return $this->fetchAll($select);
    }
}
