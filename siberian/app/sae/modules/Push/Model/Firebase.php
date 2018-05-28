<?php

/**
 * Class Push_Model_Firebase
 *
 * @method $this setEmail(string $email)
 * @method $this setCyphered(string $cyphered)
 * @method $this setAdminId(integer $adminId)
 * @method $this setProjectNumber(string $projectNumber)
 * @method integer getId()
 * @method string getEmail()
 * @method string getProjectNumber()
 */
class Push_Model_Firebase extends Core_Model_Default
{
    /**
     * @var string
     */
    public static $fakePassword = '__fake_not_saved__';

    /**
     * @var string
     */
    private static $passphrase = 'f1r3B45315my541t';

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
     * @param $email
     * @param $password
     * @return $this
     */
    public function setCredentials ($email, $password)
    {
        $publicKeyPath = Core_Model_Directory::getBasePathTo('/var/apps/certificates/keys/google-credentials.pub');
        $clear = $email . ':' . $password;
        $cyphered = \Siberian\Cypher::cypher($publicKeyPath, $clear);

        return $this->setData('cyphered', $cyphered);
    }

    /**
     * @return array
     */
    public function getCredentials ()
    {
        try {
            $privateKeyPath = Core_Model_Directory::getBasePathTo('/var/apps/certificates/keys/google-credentials');
            $cyphered = $this->getData('cyphered');
            $clear = \Siberian\Cypher::decypher($privateKeyPath, $cyphered, self::$passphrase);

            $parts = explode(':', $clear);

            return [
                'email' => $parts[0],
                'password' => $parts[1],
            ];
        } catch (\Exception $e) {
            return [
                'email' => '',
                'password' => '',
            ];
        }
    }

    /**
     * @param $rawProjects
     * @return $this
     */
    public function setRawProjects ($rawProjects)
    {
        return $this->setData('raw_projects', Siberian_Json::encode($rawProjects));
    }

    /**
     * @return array|mixed
     */
    public function getRawProjects ()
    {
        $data = $this->getData('raw_projects');

        if (!empty($data)) {
            return Siberian_Json::decode($data);
        }
        return [];
    }
}
