<?php

/**
 * Class Push_Model_Firebase
 *
 * @method $this setAdminId(integer $adminId)
 * @method $this setSenderId(string $senderId)
 * @method $this setServerKey(string $serverKey)
 * @method $this setGoogleService(string $googleService)
 * @method integer getId()
 * @method string getSenderId()
 * @method string getServerKey()
 * @method string getGoogleService()
 * @method $this find($idOrValue, $field = null)
 */
class Push_Model_Firebase extends Core_Model_Default
{
    /**
     * Push_Model_Firebase constructor.
     * @param array $datas
     */
    public function __construct ($datas = [])
    {
        parent::__construct($datas);
        $this->_db_table = 'Push_Model_Db_Table_Firebase';
    }

    /**
     * @throws \Siberian\Exception
     */
    public function checkFirebase ()
    {
        $serverKey = $this->getServerKey();
        $senderID = $this->getSenderId();
        $googleService = $this->getGoogleService();

        if (empty($serverKey) ||
            empty($senderID) ||
            empty($googleService)) {
            throw new \Siberian\Exception('#443-02: ' . __('Firebase is not well configured.'));
        }
    }

    /**
     * We extract only the required "placeholder" Application for subsequent, modifications & lighten the file!
     *
     * @param array $googleService
     * @return array
     * @throws \Siberian\Exception
     */
    public static function formatGoogleServices (array $googleService)
    {
        if (!array_key_exists('project_info', $googleService)) {
            throw new \Siberian\Exception('#443-02: ' .
                __("The provided `google-services.json` is not valid, please check the documentation to get the correct google-services.json file."));
        }

        if (array_key_exists('project_info', $googleService) &&
            !array_key_exists('firebase_url', $googleService['project_info'])) {
            throw new \Siberian\Exception('#443-03: ' .
                __("The provided `google-services.json` is not valid, please check the documentation to get the correct google-services.json file."));
        }

        $clients = $googleService['client'];

        $googleService['client'] = [];

        $placeholderClient = null;
        foreach ($clients as $client) {
            if (array_key_exists('client_info', $client) &&
                array_key_exists('android_client_info', $client['client_info']) &&
                array_key_exists('package_name', $client['client_info']['android_client_info']) &&
                $client['client_info']['android_client_info']['package_name'] === 'package.placeholder') {
                $placeholderClient = $client;
                break;
            }
        }

        if (empty($placeholderClient)) {
            throw new \Siberian\Exception(
                '#443-01: ' .
                __('The required Application `package.placeholder` was not found in the file.'));
        }

        $googleService['client'][] = $placeholderClient;

        return $googleService;
    }
}
