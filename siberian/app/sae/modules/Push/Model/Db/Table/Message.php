<?php

class Push_Model_Db_Table_Message extends Core_Model_Db_Table {

    protected $_name = "push_messages";
    protected $_primary = "message_id";
    protected $_inapp_code = "inapp_messages";

    public function getMessages($message_type) {

        $select = $this->select()
            ->from(array('am' => $this->_name))
            ->where('am.status = ?', 'queued')
            ->where('am.type_id = ?', $message_type)
//            ->where('am.delivery = NOW()')
            ->order('am.created_at')
            ->where('pdm.is_displayed = ?', '1')
            ->limit(100)
            ->setIntegrityCheck(false)
        ;

        return $this->fetchAll($select);
    }

    public function createLog($datas) {
        $select = $this->_db->select()
            ->from('push_delivered_message', array('deliver_id'))
            ->where('message_id = ?', $datas['message_id'])
            ->where('device_uid = ?', $datas['device_uid'])
        ;
        $deliver_id = $this->_db->fetchOne($select);
        if(!$deliver_id) $this->_db->insert('push_delivered_message', $datas);
    }

    public function markAsRead($device_uid,$message_id) {

        $select = $this->_db->select()
            ->from(array('pdm' => 'push_delivered_message'), array('deliver_id'))
            ->join(array('pm' => $this->_name), "pm.message_id = pdm.message_id")
            ->where('pdm.device_uid = ?', $device_uid)
            ->where('pdm.is_read = 0')
            ->where('pdm.status = 1')
        ;

        if($message_id) {
            $select->where("pm.message_id = ?",$message_id);
        }

        $deliver_ids = $this->_db->fetchCol($select);

        if(!empty($deliver_ids)) {
            $this->_db->update('push_delivered_message', array('is_read' => 1), array('deliver_id IN (?)' => $deliver_ids));
        }

        return $this;
    }

    public function findAllForFeature($appId, $typeId, $limit = 100) {

        $select = "
              SELECT *
              FROM push_messages
              WHERE app_id = '{$appId}'
              AND type_id = '{$typeId}'
              AND value_id IS NULL
              AND message_global_id IS NULL
              ORDER BY created_at DESC
              LIMIT {$limit}";

        return $this->toModelClass($this->_db->fetchAll($select));
    }

    public function findByDeviceId($device_uid, $message_type, $app_id, $offset = 0, $allowed_categories = null) {

        $select_label = $this->_db->select()
            ->from(array("tcm" => "topic_category_message"), array())
            ->join(array("_s_pntc" => "topic_category"), "tcm.category_id = _s_pntc.category_id", array("name"))
            ->where("tcm.message_id = pdm.message_id")
            ->limit(1)
        ;

        $cols = array_keys($this->_db->describeTable($this->_name));
        $cols = array_combine($cols, $cols);
        $cols["label"] = new Zend_Db_Expr("(".$select_label->assemble().")");
        unset($cols['delivered_at']);

        $select = $this->select()
            ->from(array('pdm' => 'push_delivered_message'), array('is_read', 'delivered_at'))
            ->join(array('pm' => $this->_name), "pm.message_id = pdm.message_id", $cols)
            ->where('pdm.device_uid = ?', $device_uid)
            ->where('pdm.status = 1')
            ->where('pdm.is_displayed = ?', '1')
            ->where('pm.app_id = ?', $app_id)
            ->where('pm.type_id = ?', $message_type);


        $select->limit(Push_Model_Message::DISPLAYED_PER_PAGE, $offset)
            ->order('pdm.delivered_at DESC')
            ->setIntegrityCheck(false);

        return $this->fetchAll($select);

    }

    public function markAsDisplayed($device_uid, $message_id) {

        $select = $this->_db->select()
            ->from(array('pdm' => 'push_delivered_message'))
            ->join(array('pm' => $this->_name), "pm.message_id = pdm.message_id")
            ->where('pdm.device_id = ?', $device_uid)
            ->where('pdm.message_id = ?', $message_id)
        ;
        $deliver_ids = $this->_db->fetchCol($select);

        if(!empty($deliver_ids)) {
            $this->_db->update('push_delivered_message', array('is_displayed' => 1), array('deliver_id IN (?)' => $deliver_ids));
        }

        return $this;
    }

    public function countByDeviceId($device_uid,$message_type) {

        $select = $this->_db->select()
            ->from(array('pdm' => 'push_delivered_message'), array('count' => new Zend_Db_Expr('COUNT(pdm.message_id)')))
            ->join(array('pm' => $this->_name), "pm.message_id = pdm.message_id")
            ->where('pdm.device_uid = ?', $device_uid)
            ->where('pdm.status = 1')
            ->where('pdm.is_displayed = ?', '1')
            ->where('pm.value_id = NULL')
            ->where('pdm.is_read = 0')
            ->where('pm.type_id = ?', $message_type)
        ;

        return (int) $this->_db->fetchOne($select);

    }

    public function findLastPushMessage($device_uid, $app_id) {

        $select = $this->select()
            ->from(array('pdm' => 'push_delivered_message'))
            ->join(array('pm' => $this->_name), "pm.message_id = pdm.message_id")
            ->where('pdm.device_uid = ?', $device_uid)
            ->where('pdm.status = 1')
            ->where('pdm.is_displayed = ?', '1')
            ->where('pdm.is_read = 0')
            ->where('pm.type_id = ?', '1')
            ->where('pm.app_id = ?', $app_id)
            ->order('pm.message_id DESC')
            ->limit(1)
            ->setIntegrityCheck(false)
        ;

        return $this->fetchRow($select);

    }

    public function findLastInAppMessage($app_id, $device_uid, $topics) {

        $select = $this->select()
            ->from(array('pm' => $this->_name))
            ->joinLeft(array('pdm' => 'push_delivered_message'), "pm.message_id = pdm.message_id AND pdm.device_uid = '".$device_uid."'", array())
            ->where('pdm.deliver_id IS NULL')
            ->where('pm.type_id = ?', 2)
            ->where('pm.send_at IS NULL OR pm.send_at <= ?',Zend_Date::now()->toString("yyyy-MM-dd HH:mm:ss"))
            ->where('pm.send_until >= ? OR pm.send_until is null',Zend_Date::now()->toString("yyyy-MM-dd HH:mm:ss"))
            ->where('pm.app_id = ?', $app_id);

        //PUSH TO USER ONLY
        if(Push_Model_Message::hasIndividualPush()) {
            $select->joinLeft(array("pgd" => "push_gcm_devices"), $this->_db->quoteInto("pgd.registration_id = ?", $device_uid), array())
                ->joinLeft(array("pad" => "push_apns_devices"), $this->_db->quoteInto("pad.device_uid = ?", $device_uid), array())
                ->joinLeft(array("pum" => "push_customer_message"), "pum.message_id = pm.message_id", array())
                ->where("((pum.message_id IS NOT NULL AND pum.customer_id = IFNULL(pad.customer_id, IFNULL(pgd.customer_id, 0))) OR pum.message_id IS NULL)");
        }

        if (!empty($topics)) {
            $select->joinLeft(array('pcm' => 'topic_category_message'), 'pcm.message_id = pm.message_id', array())
                ->joinLeft(array('ps' => 'topic_subscription'), 'ps.category_id = pcm.category_id AND ps.device_uid = "'.$device_uid.'"', array())
                ->where('pm.send_to_all = 1 OR (ps.category_id IN (?) AND ps.subscription_id IS NOT NULL)', implode(",", $topics))
            ;
        } else {
            $select->where('pm.send_to_all = 1');
        }

        $select->order('pm.delivered_at DESC')
            ->limit(1)
            ->setIntegrityCheck(false)
        ;

        return $this->fetchRow($select);
    }

    public function deleteLog($message_id) {

        try {
            $this->_db->delete("push_delivered_message", array("message_id = ?" => $message_id));
        } catch (Exception $e) {

        }

        return $this;
    }

    public function markInAppAsRead($app_id, $device_uid, $device_type) {

        if($device_type == 1) {
            $select = $this->_db->select()
                ->from(array("pgd" => "push_apns_devices"), array("device_id"))
                ->where("device_uid = ?", $device_uid)
            ;
            $device_id = $this->_db->fetchOne($select);
        } else {
            $select = $this->_db->select()
                ->from(array("pgd" => "push_gcm_devices"), array("device_id"))
                ->where("registration_id = ?", $device_uid)
                ->orWhere("device_uid = ?", $device_uid)
            ;
            $device_id = $this->_db->fetchOne($select);
        }

        if(!$device_id) return $this;

        $fields = array_keys($this->_db->describeTable("push_delivered_message"));
        $fields = array_combine($fields,$fields);
        unset($fields["message_id"]);
        unset($fields["deliver_id"]);

        $fields["device_id"] = new Zend_Db_Expr($device_id);
        $fields["device_uid"] = new Zend_Db_Expr('"'.$device_uid.'"');
        $fields["device_type"] = new Zend_Db_Expr($device_type);
        $fields["is_read"] = new Zend_Db_Expr("1");
        $fields["status"] = new Zend_Db_Expr("1");
        $fields["is_displayed"] = new Zend_Db_Expr("1");
        $fields["delivered_at"] = new Zend_Db_Expr('"'.Zend_Date::now()->toString("yyyy-MM-dd HH:mm:ss").'"');

        $select = $this->select()
            ->from(array('pm' => $this->_name),array("message_id" => "pm.message_id"))
            ->joinLeft(array('pdm' => 'push_delivered_message'), "pm.message_id = pdm.message_id AND pdm.device_uid = '".$device_uid."'", $fields)
            ->where('pdm.deliver_id IS NULL')
            ->where('pm.app_id = ?', $app_id)
            ->where('pm.type_id = ?', 2)
            ->setIntegrityCheck(false)
        ;

        $fields = array_merge(array("message_id"), array_keys($fields));
        $this->_db->query("INSERT INTO push_delivered_message(".implode(", ", $fields).") {$select->assemble()}");

        return $this;

    }

    public function getInAppCode(){
        $select = $this->_db->select()
            ->from(array('ao' => "application_option"),array("ao.option_id"))
            ->where("ao.code = ?", $this->_inapp_code)
        ;
        return $this->_db->fetchOne($select);
    }

    public function deleteAllMessages($app_id,$message_type) {

        try {
            $this->_db->delete("push_messages", array("app_id = ?" => $app_id,"type_id = ?" => $message_type));
        } catch (Exception $e) {}

        return $this;
    }

    public function deleteAllLogs($app_id,$message_type) {

        try {
            $select = $this->select()
                ->from("push_messages",array("message_id"))
                ->where("app_id = ?",$app_id)
                ->where("type_id = ?",$message_type)
            ;
            $ids = $this->_db->fetchCol($select);

            $this->_db->delete("push_delivered_message",array("message_id IN (?)" => $ids));

        } catch (Exception $e) {}

        return $this;
    }

}
