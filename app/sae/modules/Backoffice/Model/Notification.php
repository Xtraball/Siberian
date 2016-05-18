<?php

class Backoffice_Model_Notification extends Core_Model_Default
{

    public function __construct($datas = array()) {
        parent::__construct($datas);
        $this->_db_table = 'Backoffice_Model_Db_Table_Notification';
    }

    public function save() {
        if(!$this->getId()) {
            $this->setIsRead(0);
        }

        return parent::save();
    }

    public function isRead() {
        return $this->getData('is_read');
    }

    public function isHighPriority() {
        return $this->getData('is_high_priority');
    }

    public function update() {

        $last_id = $this->findLastId();
        $url = 'http://www.tigerappcreator.com/en/front/notification/list/type/multiapps/last_id/'.$last_id;
        try {
            if($datas = file_get_contents($url)) {
                $datas = Zend_Json::decode($datas);
                if(is_array($datas)) {
                    foreach($datas as $data) {
                        $notif = new self();
                        $notif->addData($data)->save();
                    }
                }
            }
        } catch(Exception $e) {

        }
    }

    public function countUnread() {
        return $this->getTable()->countUnread();
    }

    public function findLastId() {
        return $this->getTable()->findLastId();
    }

}