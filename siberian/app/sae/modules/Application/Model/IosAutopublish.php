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
 * @method $this setItcProvider(string $itcProvider)
 * @method string getItcProvider()
 * @method $this setRefreshPem(boolean $refreshPem)
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
     * @param $teamId
     * @param $providerId
     * @return $this
     */
    public function selectTeam ($teamId, $providerId)
    {
        $teams = Siberian_Json::decode($this->getData('teams'));

        $selectedTeamId = '';
        $selectedTeamName = '';

        if (is_array($teams)) {
            foreach ($teams['teams'] as $team) {
                if ($team['teamId'] == $teamId) {
                    $selectedTeamId = $team['teamId'];
                    $selectedTeamName = $team['name'];

                    break;
                }
            }
        }

        $this
            ->setTeamId($selectedTeamId)
            ->setTeamName($selectedTeamName)
            ->setItcProvider($providerId);

        return $this;
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
            if (isset($teams['teams'])) {
                foreach ($teams['teams'] as $team) {
                    $dataTeams[] = [
                        'teamId' => $team['teamId'],
                        'type' => $team['type'],
                        'name' => $team['name'],
                    ];
                }
            }
        }
        return $dataTeams;
    }

    /**
     * @return array
     */
    public function getItcProvidersArray ()
    {
        $teams = $this->getData('teams');
        $dataProviders = [];
        if (!empty($teams)) {
            $teams = Siberian_Json::decode($teams);
            if (isset($teams['itcTeams'])) {
                foreach ($teams['itcTeams'] as $provider) {
                    $dataProviders[] = [
                        'providerId' => $provider['contentProvider']['contentProviderId'],
                        'name' => $provider['contentProvider']['name'],
                    ];
                }
            }
        }
        return $dataProviders;
    }

    /**
     * @return array
     */
    public function getStats ()
    {
        try {
            /**
             * Example response
             * {
             *   "averageBuildtime":725,
             *   "itemsCount":11,
             *   "estimatedTime":10875
             * }
             */
            $response = Siberian_Request::get('https://autopublish-api.siberiancms.com/stats');
            $values = Siberian_Json::decode($response);

            $load = __('low');
            $loadColor = 'alert alert-success';
            if ($values['itemsCount'] > 10) {
                $load = __('average');
                $loadColor = 'alert alert-warning';
            }
            if ($values['itemsCount'] > 20) {
                $load = __('high');
                $loadColor = 'alert alert-danger';
            }

            $moment = (new \MomentPHP\MomentPHP())
                ->add($values['estimatedTime'], 'seconds');
            $fromNow = $moment->fromNow();

            $payload = [
                'isOnline' => $values['isOnline'],
                'offlineStatus' => $values['isOnline'] ? __('online') : __('maintenance'),
                'offlineMessage' => $values['offlineMessage'],
                'load' => $load,
                'loadColor' => $loadColor,
                'itemsCount' => $values['itemsCount'],
                'fromNow' => $fromNow,
                'messages' => $values['messages'],
            ];

        } catch (Exception $e) {
            $payload = [
                'isOnline' => false,
                'offlineStatus' => __('unreachable'),
                'offlineMessage' => __('iOS-Autopublish service is unreachable.'),
                'load' => __('N.A.'),
                'loadColor' => 'alert alert-info',
                'itemsCount' => 0,
                'fromNow' => 'N.A.',
                'messages' => [],
            ];
        }

        return $payload;
    }
}
