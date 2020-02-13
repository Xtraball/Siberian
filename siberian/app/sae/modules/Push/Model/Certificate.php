<?php

/**
 * Class Push_Model_Certificate
 *
 * @method integer getId()
 * @method $this setType(string $type)
 * @method $this setPath(string $path)
 */
class Push_Model_Certificate extends Core_Model_Default
{
    /**
     * @var
     */
    protected static $_ios_certificat;

    /**
     * @var
     */
    protected static $_android_key;

    /**
     * @var
     */
    protected static $_android_sender_id;

    /**
     * Push_Model_Certificate constructor.
     * @param array $datas
     */
    public function __construct($datas = [])
    {
        parent::__construct($datas);
        $this->_db_table = 'Push_Model_Db_Table_Certificate';
    }

    /**
     * @param null $app_id
     * @return array|mixed|null|string
     */
    public static function getiOSCertificat($app_id = null)
    {
        if (Siberian_Version::is("sae")) {
            $app_id = null;
        }

        if (is_null(self::$_ios_certificat)) {
            $certificat = new self();
            if (is_null($app_id)) {
                $certificat->find(['type' => 'ios']);
            } else {
                $certificat->find(['type' => 'ios', 'app_id' => $app_id]);
            }

            self::$_ios_certificat = $certificat->getPath();
        }

        return self::$_ios_certificat;
    }

    /**
     * @param null $app_id
     * @return array|null
     */
    public static function getInfos($app_id = null)
    {
        $pemInfo = null;

        try {
            if (!self::getiOSCertificat($app_id)) {
                throw new \Siberian\Exception('PEM is not set.');
            }

            $certificate = self::getiOSCertificat($app_id);
            $pemPath = path($certificate);
            if (!is_file($pemPath)) {
                throw new \Siberian\Exception('File is missing.');
            }

            $pemContent = file_get_contents($pemPath);
            $pemInfo = openssl_x509_parse($pemContent);

            if (empty($pemInfo)) {
                throw new \Siberian\Exception('PEM is unreadable.');
            }

            $pemInfo = [
                'production' => false !== stripos($pemInfo['name'], 'Development'),
                'package_name' => $pemInfo['subject']['UID'],
                'valid_from' => time_to_date($pemInfo['validFrom_time_t']),
                'valid_until' => time_to_date($pemInfo['validTo_time_t']),
                //On some certificates, oeiginal infos contains unvalid char.
                //It results that controller->_sendHtml() return null instead of json config
                //"original" => $pem_info,
                'is_valid' => ($pemInfo['validTo_time_t'] > time()),
            ];

            $pemInfo['apns_feedback'] = self::testApnsPort(2196);
            $pemInfo['test_pem'] = self::testPem($certificate);
            $pemInfo['port_open'] = self::testApnsPort(2195);

        } catch (\Exception $e) {
            $pemInfo = [
                'is_valid' => false,
            ];
        }

        return $pemInfo;
    }

    /**
     * @return bool
     */
    public static function testApnsPort($port = 2195)
    {
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
    public static function testPem($certificate)
    {
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
        } catch (ApnsPHP_Exception $e) {
            return false;
        }
    }

    /**
     * @return array|mixed|null|string
     */
    public static function getAndroidKey()
    {
        if (is_null(self::$_android_key)) {
            $certificat = new self();
            $certificat->find('android_key', 'type');
            self::$_android_key = $certificat->getPath();
        }

        return self::$_android_key;

    }

    /**
     * @return array|mixed|null|string
     */
    public static function getAndroidSenderId()
    {
        if (is_null(self::$_android_sender_id)) {
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
    public function getPath($uri = '', array $params = [], $locale = null)
    {
        return $this->getData("path");
    }
}
