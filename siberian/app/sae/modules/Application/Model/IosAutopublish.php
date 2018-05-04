<?php

/**
 * Class Application_Model_IosAutopublish
 *
 * @method integer getId()
 * @method $this setAppId(integer $appId)
 * @method $this setCypheredCredentials(string $cypheredCredentials)
 * @method $this setTeams(string $teams)
 * @method string getCypheredCredentials()
 * @method $this setItunesLogin(string $login)
 * @method $this setItunesPassword(string $password)
 * @method $this setTeamId(string $teamId)
 * @method string getTeamId()
 * @method $this setTeamName(string $teamName)
 * @method string getTeamName()
 * @method $this setWantToAutopublish(boolean $publish)
 * @method $this setHasAds(boolean $hasAds)
 * @method $this setHasBgLocate(boolean $hasBgLocate)
 * @method $this setHasAudio(boolean $hasAudio)
 * @method $this setLanguages(string $languages)
 * @method string getToken()
 * @method $this setToken(string $token)
 * @method boolean getHasAds()
 * @method boolean getWantToAutopublish()
 * @method boolean getHasBgLocate()
 * @method boolean getHasAudio()
 */
class Application_Model_IosAutopublish extends Core_Model_Default
{
    /**
     * @var string
     */
    public $autopublishApiEndpoint = 'https://autopublish-api.siberiancms.com/get-teams';

    /**
     * @var string
     */
    public static $fakePassword = '__fake_not_saved__';

    /**
     * Application_Model_IosAutopublish constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->_db_table = 'Application_Model_Db_Table_IosAutopublish';
    }

    /**
     * @param $loginOrCypher
     * @param null $password
     * @return array|mixed
     */
    public function getTeams ($loginOrCypher, $password = null)
    {
        if ($password === null) {
            $base64cypher = $loginOrCypher;
        } else {
            $base64cypher = self::cypher($loginOrCypher . ':' . $password);
        }

        $result = Siberian_Request::post(
            $this->autopublishApiEndpoint,
            [
                'credentials' => $base64cypher
            ],
            null,
            null,
            null,
            [
                'timeout' => 15
            ]);

        $response = Siberian_Json::decode($result);

        if (array_key_exists('success', $response)) {
            // Adds cyphered data to payload for further save!
            $response['cypheredCredentials'] = $base64cypher;
        }

        return $response;
    }

    /**
     * @param $clear
     * @return string
     */
    public static function cypher ($clear)
    {
        // Test credentials validity!
        $publicKeyPath = Core_Model_Directory::getBasePathTo('/var/apps/certificates/keys/autopublish-api.pub');
        $publicKey = file_get_contents($publicKeyPath);

        $rsa = new \phpseclib\Crypt\RSA();
        $rsa->loadKey($publicKey);
        $rsa->setEncryptionMode(\phpseclib\Crypt\RSA::ENCRYPTION_PKCS1);
        $base64cypher = base64_encode($rsa->encrypt($clear));

        return $base64cypher;
    }

    /**
     * @return array
     */
    public function getTeamsArray ()
    {
        $teams = $this->getData('teams');
        $dataTeams = [];
        if (!empty($teams)) {
            $teams = Siberian_Json::decode($teams);
            foreach ($teams as $team) {
                $dataTeams[] = [
                    'teamId' => $team['teamId'],
                    'type' => $team['type'],
                    'name' => $team['name'],
                ];
            }
        }
        return $dataTeams;
    }
}
