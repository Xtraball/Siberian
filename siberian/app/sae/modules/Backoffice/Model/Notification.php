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
     * Clear related notifications.
     *
     * @param $object_type
     * @param $object_id
     * @return bool
     */
    public static function clear($object_type, $object_id) {

        try {

            $model = new self();
            $notifications = $model->findAll(array(
                "object_type = ?"   => $object_type,
                "object_id = ?"     => $object_id
            ));

            foreach($notifications as $notification) {
                $notification->delete();
            }

        } catch(Exception $e) {

            return false;
        }

        return true;
    }


    /**
     * Search for an existing similar alert/message with options
     *
     * @param $object_type
     * @param $object_id
     * @param $source
     * @param $type
     * @param null $time number of seconds since the last similar alert
     */
    public static function notificationExists($object_type, $object_id, $source, $type, $time = null) {
        $model = new self();
        $options = array(
            "object_type = ?"   => $object_type,
            "object_id = ?"     => $object_id,
            "source = ?"        => $source,
            "type = ?"          => $type,
        );

        if(isset($time)) {
            $date = time_to_date(time() - $time, "YYYY-MM-dd HH:mm:ss");
            $options["created_at > ?"] = $date;
        }

        $exists = $model->findAll($options);

        return ($exists->count() > 0);
    }

    /**
     * @param $title
     * @param $message
     * @param $object_type
     * @param $object_id
     * @param $source
     * @param $type
     */
    public static function createNotification($title, $message, $object_type, $object_id, $source, $type, $is_high_priority = false, $link = null) {
        $model = new self();

        $model
            ->setTitle($title)
            ->setDescription($message)
            ->setObjectType($object_type)
            ->setObjectId($object_id)
            ->setSource($source)
            ->setType($type)
            ->setIsHighPriority($is_high_priority)
            ->setLink($link)
            ->save();

        return $model;
    }

    /**
     * @param $notification
     * @param null $subject
     * @param null $message
     */
    public static function sendEmailForNotification($notification, $subject = null, $message = null) {
        $email = new Siberian_Mail();

        if(!isset($subject)) {
            $email->setSubject($notification->getTitle());
        } else {
            $email->setSubject($subject);
        }

        if(!isset($message)) {
            $email->setBodyHtml($notification->getDescription());
        } else {
            $email->setBodyHtml($message);
        }

        /** Sends to platform owner */
        $email->ccToSender();
        $email->send();
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
     * @deprecated, url will change.
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
            $backoffice_notification_model->update();
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