<?php

class Push_Model_Db_Table_Android_Device extends Core_Model_Db_Table {

    protected $_name = "push_gcm_devices";
    protected $_primary = "device_id";

    public function findByAppId($app_id,$topics, $customers) {
        $select = $this->select()
            ->from(array('pgd' => $this->_name))
            ->where('pgd.app_id = ?', $app_id)
            ->group('pgd.device_id')
        ;

        if($topics) {
            $select->joinLeft(array("ps" => "topic_subscription"),"(ps.device_uid = pgd.registration_id OR ps.device_uid = pgd.device_uid)",array())
                    ->where("ps.category_id IN (?)",implode(",",$topics));
        }

        if($customers) {
            $select->where("pgd.customer_id IN (?)", $customers);
        }

        $select->setIntegrityCheck(false);

        return $this->fetchAll($select);
    }

    public function findNotReceivedMessages($device_uid, $geolocated) {

        $created_at = $this->_db->fetchOne($this->_db->select()->from('push_gcm_devices', array('created_at'))->where('registration_id = ?', $device_uid));

        $join = join(' AND ', array(
            'pdm.message_id = pm.message_id',
            $this->_db->quoteInto('pdm.device_uid = ?', $device_uid)
        ));

        $select = $this->_db->select()
            ->from(array('pm' => 'push_messages'), array('pm.message_id'))
            ->joinLeft(array('pdm' => 'push_delivered_message'), $join, array())
            ->where('pm.created_at >= ?', $created_at)
            ->where('pdm.is_displayed = ?', '0')
            ->where('pm.status = ?', 'delivered')
        ;

        if($geolocated === true) $select->where('pm.latitude IS NOT NULL')->where('pm.longitude IS NOT NULL')->where('pm.radius IS NOT NULL');
        else if($geolocated === false) $select->where('pm.latitude IS NULL')->where('pm.longitude IS NULL')->where('pm.radius IS NULL');

        return $this->_db->fetchCol($select);

    }

    public function countUnreadMessages($device_uid) {

        $device_type = Push_Model_Iphone_Device::DEVICE_TYPE;

        $select = $this->_db->select()
            ->from(array('pm' => 'push_messages'), array('not_read' => new Zend_Db_Expr('COUNT(pdm.deliver_id)')))
            ->join(array('pdm' => 'push_delivered_message'), 'pdm.message_id = pm.message_id AND pdm.is_read = 0', array())
            ->where('pdm.device_uid = ?', $device_uid)
            ->where('pdm.is_displayed = ?', '1')
            ->group('pdm.device_uid')
        ;

        return $this->_db->fetchOne($select);
    }

}

