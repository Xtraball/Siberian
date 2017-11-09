<?php

class Push_Model_Db_Table_Iphone_Device extends Core_Model_Db_Table {

    protected $_name = "push_apns_devices";
    protected $_primary = "device_id";

    public function findByAppId($app_id, $topics, $customers) {

        $select = $this->select()
            ->from(array('pad' => $this->_name))
            ->joinLeft(array('pdm' => 'push_delivered_message'), 'pdm.device_id = pad.device_id AND pdm.status = 1 AND pdm.is_read = 0 AND pdm.is_displayed = 1 AND pdm.device_type = 1', array('not_read' => new Zend_Db_Expr('COUNT(pdm.deliver_id)')))
            ->joinLeft(array('pm' => 'push_messages'), 'pm.message_id = pdm.message_id AND pm.type_id = 1', array())
            ->where('pad.app_id = ?', $app_id)
            ->group('pad.device_id')
        ;

        if($topics) {
            $select->joinLeft(array("ps" => "topic_subscription"),"ps.device_uid = pad.device_uid",array())
                ->where("ps.category_id IN (?)",implode(",",$topics));
        }

        if($customers) {
            $select->where("pad.customer_id IN (?)", $customers);
        }

        $select->setIntegrityCheck(false);

        return $this->fetchAll($select);
    }

    public function countUnreadMessages($device_uid) {

        $select = $this->_db->select()
            ->from(array('pm' => 'push_messages'), array('not_read' => new Zend_Db_Expr('COUNT(pad.deliver_id)')))
            ->join(array('pad' => 'push_delivered_message'), 'pad.message_id = pm.message_id AND pad.is_read = 0', array())
            ->where('pad.device_uid = ?', $device_uid)
            ->where('pm.type_id = ?', 1)
            ->group('pad.device_uid')
        ;

        return $this->_db->fetchOne($select);
    }

    public function findNotReceivedMessages($device_uid, $geolocated) {

        $created_at = $this->_db->fetchOne($this->_db->select()->from('push_apns_devices', array('created_at'))->where('device_uid = ?', $device_uid));

        $join = join(' AND ', array(
            'pdm.message_id = pm.message_id',
            $this->_db->quoteInto('pdm.device_uid = ?', $device_uid)
        ));

        $select = $this->_db->select()
            ->from(array('pm' => 'push_messages'), array('pm.message_id'))
            ->joinLeft(array('pdm' => 'push_delivered_message'), $join, array())
            ->where('pm.created_at >= ?', $created_at)
            // ->where('pdm.message_id IS NULL')
            ->where('pm.status = ?', 'delivered')
        ;

        if($geolocated === true) $select->where('pm.latitude IS NOT NULL')->where('pm.longitude IS NOT NULL')->where('pm.radius IS NOT NULL');
        else if($geolocated === false) $select->where('pm.latitude IS NULL')->where('pm.longitude IS NULL')->where('pm.radius IS NULL');

        return $this->_db->fetchCol($select);

    }

    public function hasReceivedThisMessage($device_id, $message_id) {

        $select = $this->_db->select()
            ->from(array('pm' => 'push_messages'), array())
            ->join(array('pdm' => 'push_delivered_message'), 'pdm.app_id = pm.app_id', array('pm.message_id'))
            ->where('pm.message_id = ?', $message_id)
            ->where('pdm.device_type = ?', Push_Model_Iphone_Device::DEVICE_TYPE)
        ;
        Zend_Debug::dump($this->_db->fetchOne($select));
        die;
        return $this->_db->fetchOne($select);

    }

}
