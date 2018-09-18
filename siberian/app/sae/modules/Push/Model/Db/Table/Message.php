<?php

/**
 * Class Push_Model_Db_Table_Message
 */
class Push_Model_Db_Table_Message extends Core_Model_Db_Table
{
    /**
     * @var string
     */
    protected $_name = "push_messages";

    /**
     * @var string
     */
    protected $_primary = "message_id";

    /**
     * @var string
     */
    protected $_inapp_code = "inapp_messages";

    /**
     * @param $message_type
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getMessages($message_type)
    {
        $select = $this->select()
            ->from(['am' => $this->_name])
            ->where('am.status = ?', 'queued')
            ->where('am.type_id = ?', $message_type)
            ->order('am.created_at')
            ->where('pdm.is_displayed = ?', '1')
            ->limit(100)
            ->setIntegrityCheck(false);

        return $this->fetchAll($select);
    }

    /**
     * @param $datas
     * @throws Zend_Db_Adapter_Exception
     */
    public function createLog($datas)
    {
        $select = $this->_db->select()
            ->from('push_delivered_message', ['deliver_id'])
            ->where('message_id = ?', $datas['message_id'])
            ->where('device_uid = ?', $datas['device_uid']);
        $deliver_id = $this->_db->fetchOne($select);
        if (!$deliver_id) $this->_db->insert('push_delivered_message', $datas);
    }

    /**
     * @param $device_uid
     * @param $message_id
     * @return $this
     * @throws Zend_Db_Adapter_Exception
     */
    public function markAsRead($device_uid, $message_id)
    {
        $select = $this->_db->select()
            ->from(['pdm' => 'push_delivered_message'], ['deliver_id'])
            ->where('pdm.device_uid = ?', $device_uid)
            ->where('pdm.is_read = 0')
            ->where('pdm.status = 1');

        if ($message_id) {
            $select->where("pdm.message_id = ?", $message_id);
        }

        $deliver_ids = $this->_db->fetchCol($select);

        if (!empty($deliver_ids)) {
            $this->_db->update('push_delivered_message', ['is_read' => 1], ['deliver_id IN (?)' => $deliver_ids]);
        }

        return $this;
    }

    /**
     * @param $appId
     * @param $typeId
     * @param int $limit
     * @return mixed
     */
    public function findAllForFeature($appId, $typeId, $limit = 100)
    {
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

    /**
     * @param $device_uid
     * @param $message_type
     * @param $app_id
     * @param int $offset
     * @param null $allowed_categories
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function findByDeviceId($device_uid, $message_type, $app_id, $offset = 0, $allowed_categories = null)
    {
        $select_label = $this->_db->select()
            ->from(["tcm" => "topic_category_message"], [])
            ->join(["_s_pntc" => "topic_category"], "tcm.category_id = _s_pntc.category_id", ["name"])
            ->where("tcm.message_id = pdm.message_id")
            ->limit(1);

        $cols = array_keys($this->_db->describeTable($this->_name));
        $cols = array_combine($cols, $cols);
        $cols["label"] = new Zend_Db_Expr("(" . $select_label->assemble() . ")");
        unset($cols['delivered_at']);

        $select = $this->select()
            ->from(['pdm' => 'push_delivered_message'], ['is_read', 'delivered_at'])
            ->joinLeft(['pm' => $this->_name], "pm.message_id = pdm.message_id", $cols)
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

    /**
     * @param $device_uid
     * @param $message_id
     * @return $this
     * @throws Zend_Db_Adapter_Exception
     */
    public function markAsDisplayed($device_uid, $message_id)
    {
        $select = $this->_db->select()
            ->from(['pdm' => 'push_delivered_message', ['deliver_id']])
            ->where('pdm.device_id = ?', $device_uid)
            ->where('pdm.message_id = ?', $message_id);
        $deliver_ids = $this->_db->fetchCol($select);

        if (!empty($deliver_ids)) {
            $this->_db->update('push_delivered_message', ['is_displayed' => 1], ['deliver_id IN (?)' => $deliver_ids]);
        }

        return $this;
    }

    /**
     * @param $device_uid
     * @param $message_type
     * @return int
     */
    public function countByDeviceId($device_uid, $message_type)
    {
        $select = $this->_db->select()
            ->from(['pdm' => 'push_delivered_message'], ['count' => new Zend_Db_Expr('COUNT(pdm.message_id)')])
            ->joinLeft(['pm' => $this->_name], "pm.message_id = pdm.message_id")
            ->where('pdm.device_uid = ?', $device_uid)
            ->where('pdm.status = 1')
            ->where('pdm.is_displayed = ?', '1')
            ->where('pm.value_id = NULL')
            ->where('pdm.is_read = 0')
            ->where('pm.type_id = ?', $message_type);

        return (int)$this->_db->fetchOne($select);
    }

    /**
     * @param $device_uid
     * @param $app_id
     * @return null|Zend_Db_Table_Row_Abstract
     */
    public function findLastPushMessage($device_uid, $app_id)
    {
        $select = $this->select()
            ->from(['pdm' => 'push_delivered_message'])
            ->joinLeft(['pm' => $this->_name], "pm.message_id = pdm.message_id")
            ->where('pdm.device_uid = ?', $device_uid)
            ->where('pdm.status = 1')
            ->where('pdm.is_displayed = ?', '1')
            ->where('pdm.is_read = 0')
            ->where('pm.type_id = ?', '1')
            ->where('pm.app_id = ?', $app_id)
            ->order('pm.message_id DESC')
            ->limit(1)
            ->setIntegrityCheck(false);

        return $this->fetchRow($select);
    }

    /**
     * @param $app_id
     * @param $device_uid
     * @param $topics
     * @return null|Zend_Db_Table_Row_Abstract
     */
    public function findLastInAppMessage($app_id, $device_uid, $topics)
    {
        if (empty($device_uid)) {
            $device_uid = '-rng-';
        }

        $select = $this->select()
            ->from(['pm' => $this->_name])
            ->joinLeft(['pdm' => 'push_delivered_message'], "pm.message_id = pdm.message_id AND pdm.device_uid = '$device_uid'", [])
            ->where('pdm.deliver_id IS NULL')
            ->where('pm.type_id = ?', 2)
            ->where('pm.send_at IS NULL OR pm.send_at <= ?', Zend_Date::now()->toString("yyyy-MM-dd HH:mm:ss"))
            ->where('pm.send_until >= ? OR pm.send_until is null', Zend_Date::now()->toString("yyyy-MM-dd HH:mm:ss"))
            ->where('pm.app_id = ?', $app_id);

        //PUSH TO USER ONLY
        if (Push_Model_Message::hasIndividualPush()) {
            $select
                ->joinLeft(["pgd" => "push_gcm_devices"], $this->_db->quoteInto("pgd.registration_id = ?", $device_uid), [])
                ->joinLeft(["pad" => "push_apns_devices"], $this->_db->quoteInto("pad.device_uid = ?", $device_uid), [])
                ->joinLeft(["pum" => "push_customer_message"], "pum.message_id = pm.message_id", [])
                ->where("((pum.message_id IS NOT NULL AND pum.customer_id = IFNULL(pad.customer_id, IFNULL(pgd.customer_id, 0))) OR pum.message_id IS NULL)");
        }

        if (!empty($topics)) {
            $select
                ->joinLeft(['pcm' => 'topic_category_message'], 'pcm.message_id = pm.message_id', [])
                ->joinLeft(['ps' => 'topic_subscription'], 'ps.category_id = pcm.category_id AND ps.device_uid = "' . $device_uid . '"', [])
                ->where('pm.send_to_all = 1 OR (ps.category_id IN (?) AND ps.subscription_id IS NOT NULL)', implode(",", $topics));
        } else {
            $select->where('pm.send_to_all = 1');
        }

        $select->order('pm.delivered_at DESC')
            ->limit(1)
            ->setIntegrityCheck(false);

        return $this->fetchRow($select);
    }

    /**
     * @param $message_id
     * @return $this
     */
    public function deleteLog($message_id)
    {
        try {
            $this->_db->delete("push_delivered_message", ["message_id = ?" => $message_id]);
        } catch (Exception $e) {}

        return $this;
    }

    /**
     * @param $app_id
     * @param $device_uid
     * @param $device_type
     * @return $this
     */
    public function markInAppAsRead($app_id, $device_uid, $device_type)
    {
        if ($device_type == 1) {
            $select = $this->_db->select()
                ->from(["pgd" => "push_apns_devices"], ["device_id"])
                ->where("device_uid = ?", $device_uid);
            $device_id = $this->_db->fetchOne($select);
        } else {
            $select = $this->_db->select()
                ->from(["pgd" => "push_gcm_devices"], ["device_id"])
                ->where("registration_id = ?", $device_uid)
                ->orWhere("device_uid = ?", $device_uid);
            $device_id = $this->_db->fetchOne($select);
        }

        if (!$device_id) {
            return $this;
        }

        $fields = array_keys($this->_db->describeTable("push_delivered_message"));
        $fields = array_combine($fields, $fields);
        unset($fields["message_id"]);
        unset($fields["deliver_id"]);

        $fields["device_id"] = new Zend_Db_Expr($device_id);
        $fields["device_uid"] = new Zend_Db_Expr('"' . $device_uid . '"');
        $fields["device_type"] = new Zend_Db_Expr($device_type);
        $fields["is_read"] = new Zend_Db_Expr("1");
        $fields["status"] = new Zend_Db_Expr("1");
        $fields["is_displayed"] = new Zend_Db_Expr("1");
        $fields["delivered_at"] = new Zend_Db_Expr('"' . Zend_Date::now()->toString("yyyy-MM-dd HH:mm:ss") . '"');

        $select = $this->select()
            ->from(['pm' => $this->_name], ["message_id" => "pm.message_id"])
            ->joinLeft(['pdm' => 'push_delivered_message'], "pm.message_id = pdm.message_id", $fields)
            ->where('pdm.device_uid = ?', $device_uid)
            ->where('pdm.deliver_id IS NULL')
            ->where('pm.app_id = ?', $app_id)
            ->where('pm.type_id = ?', 2)
            ->setIntegrityCheck(false);

        $fields = array_merge(["message_id"], array_keys($fields));
        $query = "INSERT INTO push_delivered_message(" . implode(", ", $fields) . ") {$select->assemble()}";
        $this->_db->query($query);

        return $this;
    }

    /**
     * @param $messageId
     * @param $deviceUid
     * @throws Zend_Exception
     */
    public function markRealInAppAsRead($messageId, $deviceUid)
    {
        $pushDeliveredMessage = new Push_Model_DeliveredMessage();

        $pushDeliveredMessage
            ->setDeviceId(0)
            ->setDeviceUid($deviceUid)
            ->setDeviceType(0)
            ->setMessageId($messageId)
            ->setStatus(1)
            ->setIsRead(1)
            ->setIsDisplayed(1)
            ->setDeliveredAt(Zend_Date::now()->toString('yyyy-MM-dd HH:mm:ss'))
            ->save();
    }

    /**
     * @return string
     */
    public function getInAppCode()
    {
        $select = $this->_db->select()
            ->from(['ao' => "application_option"], ["ao.option_id"])
            ->where("ao.code = ?", $this->_inapp_code);
        return $this->_db->fetchOne($select);
    }

    /**
     * @param $app_id
     * @param $message_type
     * @return $this
     */
    public function deleteAllMessages($app_id, $message_type)
    {
        try {
            $this->_db->delete("push_messages", ["app_id = ?" => $app_id, "type_id = ?" => $message_type]);
        } catch (Exception $e) {}

        return $this;
    }

    /**
     * @param $app_id
     * @param $message_type
     * @return $this
     */
    public function deleteAllLogs($app_id, $message_type)
    {
        try {
            $select = $this->select()
                ->from("push_messages", ["message_id"])
                ->where("app_id = ?", $app_id)
                ->where("type_id = ?", $message_type);
            $ids = $this->_db->fetchCol($select);

            $this->_db->delete("push_delivered_message", ["message_id IN (?)" => $ids]);

        } catch (Exception $e) {
        }

        return $this;
    }

}
