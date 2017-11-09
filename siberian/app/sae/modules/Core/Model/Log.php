<?php

class Core_Model_Log extends Core_Model_Default
{
    public function __construct($datas = array()) {
        parent::__construct($datas);
        $this->_db_table = 'Core_Model_Db_Table_Log';
    }

    public function save() {

        $this->setRemoteAddr($this->getRemoteAddr())
            ->setVisitedAt($this->formatDate(null, 'y-MM-dd HH:mm:ss'))
        ;

        return parent::save();
    }

    public function countConsultation($device = null, $start = null, $end = null) {
        return $this->getTable()->countConsultation($device, $start, $end);
    }

    public function countNumberOfInstallations() {
        return $this->getTable()->countNumberOfInstallations();
    }

    public function getMostViewed($admin_id, $device = 'mobile', $start = null, $end = null) {
        return $this->getTable()->getMostViewed($admin_id, $device, $start, $end);
    }

    public function getRemoteAddr() {

        if (isset($_SERVER)) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } else {
            if (getenv('HTTP_X_FORWARDED_FOR')) {
                $ip = getenv('HTTP_X_FORWARDED_FOR');
            } elseif (getenv('HTTP_CLIENT_IP')) {
                $ip = getenv('HTTP_CLIENT_IP');
            } else {
                $ip = getenv('REMOTE_ADDR');
            }
        }

        return $ip;

    }
}