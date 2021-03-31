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

            $app = (new Application_Model_Application())->find($app_id);

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

            $pemInfo['test_pem'] = self::testPem($certificate, $app->getBundleId());

        } catch (\Exception $e) {
            $pemInfo = [
                'is_valid' => false,
            ];
        }

        return $pemInfo;
    }

    public static function testHttp2 ()
    {
        try {
            if (!defined('CURL_HTTP_VERSION_2_0')) {
                define('CURL_HTTP_VERSION_2_0', 3);
            }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
            $host = 'https://api.development.push.apple.com';
            curl_setopt_array($ch, [
                CURLOPT_URL => "$host/3/device/74fbf7e296f6c94755832a48476182e4e9586a380116e18a46531b62349504f1",
                CURLOPT_PORT => 443,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode([]),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HEADER => 1
            ]);

            $result = curl_exec($ch);
            if ($result === false) {
                throw new \Exception('Curl failed with error: ' . curl_error($ch));
            }

            if (strpos($result, 'HTTP/2') !== 0) {
                throw new \Exception('Request doesn\'t match HTTP/2');
            }

            $payload = [
                'success' => true,
                'message' => 'Available',
                'result' => $result
            ];
        } catch (\Exception $e) {
            $payload = [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }

        return $payload;
    }

    /**
     * @param $certificate
     * @param $bundleId
     * @return array
     */
    public static function testPem($certificate, $bundleId): array
    {
        try {
            $apnsService = new Siberian_Service_Push_Apns(path($certificate));
            $results = $apnsService->connection->send([
                '74fbf7e296f6c94755832a48476182e4e9586a380116e18a46531b62349504f1' // invalid
            ], [
                'aps' => [
                    'alert' => 'pem-test',
                    'sound' => 'default',
                ]
            ], [
                'apns-topic' => $bundleId
            ]);
            $apnsService->connection->close();
            if ($results && $results[0] && $results[0]->reason) {
                return [
                    'success' => true,
                    'message' => p__('push', 'Success')
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }

        return [
            'success' => false,
            'message' => p__('push', 'Unkown error.')
        ];
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
