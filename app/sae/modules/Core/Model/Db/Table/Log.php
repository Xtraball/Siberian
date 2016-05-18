<?php

class Core_Model_Db_Table_Log extends Core_Model_Db_Table
{
    protected $_name = "log";
    protected $_primary = "log_id";

    public function countConsultation($device, $start, $end) {

        $select = $this->select()
            ->from(array('l' => $this->_name), array('count' => new Zend_Db_Expr('COUNT(log_id)')))
            ->setIntegrityCheck(false)
        ;
        if($start) $select->where('visited_at >= ?', $start);
        if($end) $select->where('visited_at <= ?', $end);
        if($device) $select->where('device_name = ?', $device);

        return (int) $this->_db->fetchOne($select);
    }

    public function countNumberOfInstallations() {

        $select = $this->select()
            ->from(array('l' => 'push_apns_devices'), array('count' => new Zend_Db_Expr('COUNT(device_id)')))
            ->where('status = "active"')
            ->setIntegrityCheck(false)
        ;
        $iOS = (int) $this->_db->fetchOne($select);

        $select = $this->select()
            ->from(array('l' => 'push_gcm_devices'), array('count' => new Zend_Db_Expr('COUNT(device_id)')))
            ->where('status = "active"')
            ->setIntegrityCheck(false)
        ;

        $android = (int) $this->_db->fetchOne($select);

        return ($iOS + $android);
    }

    public function getMostViewed($admin_id, $device, $start, $end) {

        $select = $this->select();
        $fields = array(
            'count' => new Zend_Db_Expr('COUNT(log_id)'),
            'uri',
            'page_name'
        );
        $select->from(array('l' => $this->_name), $fields)
            ->where('visited_at >= ?', $start)
            ->where('visited_at <= ?', $end)
            ->where('device_type = ?', $device)
            ->group('page_name')
            ->order('count DESC')
            ->limit(4)
            ->setIntegrityCheck(false)
        ;

        return $this->fetchAll($select);

    }

}