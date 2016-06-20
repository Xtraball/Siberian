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

    public static function getInfos($app_id = null) {
        $pem_info = null;

        if(self::getiOSCertificat($app_id)) {
            $certificate = self::getiOSCertificat($app_id);
            $pem_content = file_get_contents(Core_Model_Directory::getBasePathTo($certificate));

            $pem_info = openssl_x509_parse($pem_content);
            if(!empty($pem_info)) {
                $pem_info = array(
                    "production" => preg_match("/Development/i", $pem_info["name"]),
                    "package_name" => $pem_info["subject"]["UID"],
                    "valid_from" => time_to_date($pem_info["validFrom_time_t"]),
                    "valid_until" => time_to_date($pem_info["validTo_time_t"]),
                    //On some certificates, oeiginal infos contains unvalid char.
                    //It results that controller->_sendHtml() return null instead of json config
                    //"original" => $pem_info,
                    "is_valid" => ($pem_info["validTo_time_t"] > time()),
                );
            } else {
                $pem_info = array(
                    "is_valid" => false,
                );
            }
        }

        return $pem_info;
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
