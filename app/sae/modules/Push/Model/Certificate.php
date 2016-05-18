<?php

class Push_Model_Certificate extends Core_Model_Default {

    protected static $_ios_certificat;
    protected static $_android_key;
    protected static $_android_sender_id;

    public function __construct($datas = array()) {
        parent::__construct($datas);
        $this->_db_table = 'Push_Model_Db_Table_Certificate';
    }

    public static function getiOSCertificat($app_id = null) {
        if(is_null(self::$_ios_certificat)) {
            $certificat = new self();
            if(is_null($app_id)) {
                $certificat->find(array('type' => 'ios'));
            } else {
                $certificat->find(array('type' => 'ios', 'app_id' => $app_id));
            }

            self::$_ios_certificat = $certificat->getPath();
        }

        return self::$_ios_certificat;
    }

    public static function getAndroidKey() {

        if(is_null(self::$_android_key)) {
            $certificat = new self();
            $certificat->find('android_key', 'type');
            self::$_android_key = $certificat->getPath();
        }

        return self::$_android_key;

    }

    public static function getAndroidSenderId() {

        if(is_null(self::$_android_sender_id)) {
            $certificat = new self();
            $certificat->find('android_sender_id', 'type');
            self::$_android_sender_id = $certificat->getPath();
        }

        return self::$_android_sender_id;

    }

    public function getPath() {
        return $this->getData("path");
    }

}