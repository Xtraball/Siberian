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
        if(Siberian_Version::is("sae")) {
            $app_id = null;
        }

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

                $pem_info["apns_feedback"] = self::testApnsPort(2196);
                $pem_info["test_pem"] = self::testPem($certificate);

            } else {
                $pem_info = array(
                    "is_valid" => false,
                );
            }

            $pem_info["port_open"] = self::testApnsPort(2195);
        }

        return $pem_info;
    }

    /**
     * @return bool
     */
    public static function testApnsPort($port = 2195) {
        $host = 'gateway.push.apple.com';

        $connection = @fsockopen($host, $port, $errno, $errstr, 2);
        if (is_resource($connection)) {
            fclose($connection);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Connection test
     *
     * @param $certificate
     * @return bool
     */
    public static function testPem($certificate) {
        require_once Core_Model_Directory::getBasePathTo('lib/ApnsPHP/Autoload.php');

        $nEnvironment = (APPLICATION_ENV == "production") ? ApnsPHP_Push::ENVIRONMENT_PRODUCTION : ApnsPHP_Push::ENVIRONMENT_SANDBOX;

        try {
            $push_service = new ApnsPHP_Push(
                $nEnvironment,
                Core_Model_Directory::getBasePathTo($certificate)
            );
            $push_service->setConnectTimeout(2);
            $push_service->setConnectRetryTimes(1);
            $push_service->connect();
            $push_service->disconnect();

            return true;
        } catch(ApnsPHP_Exception $e) {
            return false;
        }
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

    /**
     * Unused params, only for compat with abstract class
     *
     * @param string $uri
     * @param array $params
     * @param null $locale
     * @return array|mixed|null|string
     */
    public function getPath($uri = '', array $params = array(), $locale = null) {
        return $this->getData("path");
    }

}
