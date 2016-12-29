<?php

class Backoffice_Model_Notification extends Core_Model_Default {

    /**
     * Backoffice_Model_Notification constructor.
     * @param array $datas
     */
    public function __construct($datas = array()) {
        parent::__construct($datas);
        $this->_db_table = 'Backoffice_Model_Db_Table_Notification';
    }

    /**
     * @return $this|void
     */
    public function save() {
        if(!$this->getId()) {
            $this->setIsRead(0);
        }

        # Do not save dupes.
        if(!$this->getId() && ($this->getSource() == "cron") && ($this->getType() == "alert")) {
            # Check for duplicates.
            $model = new self();
            $existing = $model->find(array(
                "object_type" => $this->getObjectType(),
                "object_id" => $this->getObjectId()
            ));

            if($existing->getId()) {
                # Avoid dupes.
                return;
            }
        }

        return parent::save();
    }

    /**
     * @return array|mixed|null|string
     */
    public function isRead() {
        return $this->getData('is_read');
    }

    /**
     * @return array|mixed|null|string
     */
    public function isHighPriority() {
        return $this->getData('is_high_priority');
    }

    /**
     *
     */
    public function update() {

        $type = (Siberian_Version::is("PE")) ? "platform" : "multiapps";

        $last_id = $this->findLastId();
        $url = 'http://www.tigerappcreator.com/en/front/notification/list/type/'.$type.'/last_id/'.$last_id;
        try {
            if($datas = file_get_contents($url)) {
                $datas = Zend_Json::decode($datas);
                if(is_array($datas)) {
                    unset($datas["type"]);
                    foreach($datas as $data) {
                        $notif = new self();
                        $notif
                            ->addData($data)
                            ->save();
                    }
                }
            }
        } catch(Exception $e) {

        }
    }

    /**
     * @return array
     */
    public static function getMessages() {
        try {

            $backoffice_notification_model = new self();
            $messages = $backoffice_notification_model->findAll(
                array("is_read = ?" => 0),
                array("created_at DESC", "original_notification_id DESC"),
                array("limit" => 10)
            );

            $unread_messages = array();
            foreach ($messages as $message) {
                $link = $message->getLink();
                if(strpos($link, "updates.siberiancms.com") !== false) {
                    $link = str_replace("http://", "https://", $link);
                }

                $unread_messages[] = array(
                    "id"            => $message->getId(),
                    "title"         => $message->getTitle(),
                    "description"   => $message->getDescription(),
                    "link"          => $link,
                    "priority"      => ($message->getIsHighPriority()),
                    "source"        => $message->getSource(),
                    "type"          => $message->getType(),
                    "created_at"    => $message->getFormattedCreatedAt(Zend_Date::DATETIME_SHORT)
                );
            }

        } catch(Exception $e) {
            $unread_messages = array();
        }

        return $unread_messages;
    }

    /**
     * @return mixed
     */
    public function countUnread() {
        return $this->getTable()->countUnread();
    }

    /**
     * @return mixed
     */
    public function markRead() {
        return $this->getTable()->markRead();
    }

    /**
     * @return mixed
     */
    public function findLastId() {
        return $this->getTable()->findLastId();
    }

}