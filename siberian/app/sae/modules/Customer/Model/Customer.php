<?php

use Siberian\UUID;

/**
 * Class Customer_Model_Customer
 *
 * @method integer getId()
 * @method Customer_Model_Db_Table_Customer getTable()
 * @method string getEmail()
 */
class Customer_Model_Customer extends Core_Model_Default
{
    /**
     *
     */
    const IMAGE_PATH = '/images/customer';

    /**
     * @var array
     */
    protected $_social_datas = [];

    /**
     * @var array
     */
    protected $_metadatas = [];

    /**
     * @var array
     */
    protected $_types = ['facebook'];

    /**
     * @var array
     */
    protected $_social_instances = [];

    /**
     * @var string
     */
    protected $_db_table = Customer_Model_Db_Table_Customer::class;

    /**
     * @param $valueId
     * @return array
     */
    public function getInappStates($valueId): array
    {
        $inAppStates = [
            [
                'state' => 'my-account',
                'offline' => true,
                'params' => [],
            ],
        ];

        return $inAppStates;
    }

    /**
     * @param $app_id
     * @return array
     * @throws Zend_Db_Select_Exception
     * @throws Zend_Db_Statement_Exception
     */
    public function findByAppId($app_id)
    {
        return $this->getTable()->findByAppId($app_id);
    }

    /**
     * @param $sessionUuid
     * @return mixed
     */
    public function updateSessionUuid($sessionUuid)
    {
        // Skip overview/webapp session_uuid
        if (array_key_exists('HTTP_REFERER', $_SERVER) &&
            stripos($_SERVER['HTTP_REFERER'], '/overview/') !== false) {
            return $this;
        }
        // Skip webapp session_uuid
        if (array_key_exists('HTTP_REFERER', $_SERVER) &&
            stripos($_SERVER['HTTP_REFERER'], '/browser/') !== false) {
            return $this;
        }

        // Clear all users with this token, then update the current one!
        $this->getTable()->clearBySessionUuid($sessionUuid);

        return $this->setData('session_uuid', $sessionUuid)->save();
    }

    /**
     * @return mixed
     */
    public function clearSessionUuid()
    {
        return $this->setData('session_uuid', null)->save();
    }

    /**
     * @param $email
     * @return $this|null
     */
    public function findByEmail($email)
    {
        return $this->find($email, 'email');
    }

    /**
     * @param $id
     * @param $type
     * @param $app_id
     * @return $this
     */
    public function findBySocialId($id, $type, $app_id)
    {
        $datas = $this->getTable()->findBySocialId($id, $type, $app_id);
        if (!empty($datas)) {
            $this
                ->setData($datas)
                ->setId($datas['customer_id']);
        }

        return $this;
    }

    /**
     * @param null $values
     * @param null $params
     * @return mixed
     */
    public function findAllCustomersByApp($values = null, $params = null)
    {
        return $this->getTable()->findAllCustomersByApp($values, $params);
    }

    /**
     * @param $appId
     * @param array $params
     * @return Customer_Model_Customer[]
     * @throws Zend_Db_Select_Exception
     * @throws Zend_Db_Statement_Exception
     * @throws Zend_Db_Table_Exception
     * @throws Zend_Exception
     */
    public function findAllForApp($appId, $params = [])
    {
        return $this->getTable()->findAllForApp($appId, $params);
    }

    /**
     * @param $appId
     * @param array $params
     * @return mixed
     */
    public function countAllForApp($appId, $params = [])
    {
        return $this->getTable()->countAllForApp($appId, $params);
    }

    /**
     * @param string $firstname
     * @return $this
     * @throws \rock\sanitize\SanitizeException
     */
    public function setFirstname($firstname)
    {
        $_filtered = \rock\sanitize\Sanitize::removeTags()->sanitize($firstname);
        return $this->setData('firstname', $_filtered);
    }

    /**
     * @return string
     * @throws \rock\sanitize\SanitizeException
     */
    public function getFirstname()
    {
        return \rock\sanitize\Sanitize::removeTags()->sanitize($this->getData('firstname'));
    }

    /**
     * @param string $firstname
     * @return $this
     * @throws \rock\sanitize\SanitizeException
     */
    public function setLastname($lastname)
    {
        $_filtered = \rock\sanitize\Sanitize::removeTags()->sanitize($lastname);
        return $this->setData('lastname', $_filtered);
    }

    /**
     * @return string
     * @throws \rock\sanitize\SanitizeException
     */
    public function getLastname()
    {
        return \rock\sanitize\Sanitize::removeTags()->sanitize($this->getData('lastname'));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getFirstname() . ' ' . $this->getLastname();
    }

    /**
     * @return mixed|null
     */
    public function getFacebook()
    {
        return $this->getSocialObject('facebook');
    }

    /**
     * @param $password
     * @return bool
     */
    public function authenticate($password)
    {
        return $this->_checkPassword($password, $this->getPassword());
    }

    /**
     * @return bool
     */
    public function canAccessLockedFeatures(): bool
    {
        return $this->getData('can_access_locked_features') ||
            $this->getApplication()->getAllowAllCustomersToAccessLockedFeatures();
    }

    /**
     * @param $name
     * @param array $params
     * @return mixed|null
     */
    public function getSocialObject($name, $params = [])
    {
        if (empty($this->_social_instances[$name])) {
            if (empty($params)) {
                $params = $this->getSocialDatas($name);
            }
            if (in_array($name, $this->_types)) {
                $social_datas = !empty($params['datas']) ? $params['datas'] : [];
                $class = 'Customer_Model_Customer_Type_' . ucfirst($name);
                $this->_social_instances[$name] = new $class([
                    'social_id' => $params['social_id'],
                    'social_datas' => $social_datas,
                    'application' => $this->getApplication()
                ]);
                $this->_social_instances[$name]->setCustomer($this);
            }
        }

        return !empty($this->_social_instances[$name]) ? $this->_social_instances[$name] : null;
    }

    /**
     * @param null $name
     * @return array|mixed|null
     */
    public function getSocialDatas($name = null)
    {
        if (!$this->getId()) {
            return null;
        }
        if (is_null($name)) {
            return $this->_social_datas;
        }
        if (empty($this->_social_datas[$name])) {
            $this->_social_datas = $this->getTable()->findSocialDatas($this->getId());
        }
        return !empty($this->_social_datas[$name]) ? $this->_social_datas[$name] : null;
    }

    /**
     *
     * @param array $datas => ('type' => 'facebook', 'id' => $id, 'data' => $data);
     * @return Customer_Model_Customer
     */
    public function setSocialDatas(array $datas)
    {
        $this->_social_datas = $datas;
        return $this;
    }

    /**
     *
     * @param string $type 'facebook', etc...
     * @param array $datas => ('id' => $id, 'data' => $data);
     * @return Customer_Model_Customer
     */
    public function setSocialData($type, array $datas)
    {
        $this->_social_datas[$type] = $datas;
        return $this;
    }

    /**
     * @return bool
     */
    public function canPostSocialMessage()
    {
        foreach ($this->_types as $type) {
            if ($this->getSocialObject($type)->isValid()) {
                return true;
            }
        }
        return false;

    }

    /**
     * @param $customer_message
     * @param $message_type
     * @param null $points
     * @return $this
     */
    public function addSocialPost($customer_message, $message_type, $points = null)
    {
        if ($this->canPostSocialMessage()) {
            $this->getTable()->addSocialPost($this->getId(), $customer_message, $message_type, $points);
        }
        return $this;
    }

    /**
     * @param $post_id
     * @return $this
     */
    public function deleteSocialPost($post_id)
    {
        $this->getTable()->deleteSocialPost($this->getId(), $post_id);
        return $this;
    }

    /**
     * @param $pos
     * @param $datas
     * @return bool
     */
    public function postSocialMessage($pos, $datas)
    {
        $isOk = true;

        foreach ($this->_types as $type) {
            if ($social = $this->getSocialObject($type) && $social->isValid()) {

                $message = $social->prepareMessage($datas['message_type'], $pos, $datas['points']);
                $customer_message = !empty($datas['customer_message']) ? $datas['customer_message'] : "";
                $social->postMessage($pos, $customer_message, $message);
            }
        }

        return $isOk;
    }

    /**
     * @return mixed
     */
    public function findAllPosts()
    {
        return $this->getTable()->findAllPosts();
    }

    /**
     * @param null $app_id
     * @return mixed
     */
    public function findAllWithDeviceUid($app_id = null)
    {
        return $this->getTable()->findAllWithDeviceUid($app_id);
    }

    /**
     * @param $password
     * @return bool
     */
    public function isSamePassword($password)
    {
        return $this->getPassword() === $this->_encrypt($password);
    }

    /**
     * @param $password
     * @return $this
     * @throws Exception
     */
    public function setPassword($password)
    {
        if (strlen($password) < 6) throw new Exception($this->_('The password must be at least 6 characters'));
        $this->setData('password', $this->_encrypt($password));
        return $this;
    }

    /**
     * @return string
     */
    public function getImagePath()
    {
        return Core_Model_Directory::getPathTo(self::IMAGE_PATH);
    }

    /**
     * @return string
     */
    public function getBaseImagePath()
    {
        return Core_Model_Directory::getBasePathTo(self::IMAGE_PATH);
    }

    /**
     * @return string
     */
    public function getImageLink()
    {
        if ($this->getData('image') && is_file($this->getBaseImagePath() . '/' . $this->getImage())) {
            return $this->getImagePath() . '/' . $this->getImage();
        }
        return $this->getNoImage();
    }

    /**
     * @return null|string
     */
    public function getFullImagePath()
    {
        if ($this->getData('image') && is_file($this->getBaseImagePath() . '/' . $this->getImage())) {
            return $this->getBaseImagePath() . '/' . $this->getImage();
        }
        return null;
    }

    /**
     * @return string
     */
    public function getNoImage()
    {
        return $this->getImagePath() . '/placeholder/no-image.png';
    }

    /**
     * @param bool $sanityCheck
     * @return $this|void
     */
    public function save($sanityCheck = true)
    {
        parent::save();
        if (!empty($this->_social_datas)) {
            $datas = [];
            foreach ($this->_social_datas as $type => $data) {
                $datas[] = [
                    'type' => $type,
                    'social_id' => $data['id'],
                    'datas' => serialize(!empty($data['datas']) ? $data['datas'] : [])
                ];
            }
            $this->getTable()->insertSocialDatas($this->getId(), $datas, $this->getApplication()->getId());
        }
        if (is_array($this->_metadatas) && !empty($this->_metadatas)) {
            $datas = [];
            foreach ($this->_metadatas as $module_code => $data) {
                $datas[] = [
                    'code' => $module_code,
                    'datas' => serialize(!empty($data) ? $data : null)
                ];
            }
            $this->getTable()->insertMetadatas($this->getId(), $datas);
        }
    }

    /**
     * Generating a fresh session UUID
     *
     * @return string
     */
    public function refreshSessionUuid (): string
    {
        $newUuid = UUID::v4();

        $this->setSessionUuid($newUuid)->save();

        return $newUuid;
    }

    /**
     * @param $password
     * @param $hash
     * @return bool
     */
    private function _checkPassword($password, $hash)
    {
        return $this->_encrypt($password) === $hash;
    }

    /**
     * @param $password
     * @return string
     */
    private function _encrypt($password)
    {
        return sha1($password);
    }

    /**
     * @return mixed
     */
    public function getAppIdByCustomerId()
    {
        return $this->getTable()->getAppIdByCustomerId();
    }

    /**
     * @param null $module_code
     * @return null
     */
    public function getMetadatas($module_code = null)
    {
        if (empty($this->_metadatas) || (!empty($module_code) && empty($this->_metadatas[$module_code]))) {
            $this->_metadatas = $this->getTable()->findMetadatas($this->getId());
        }
        if (!$this->getId()) {
            return null;
        }
        if (is_null($module_code)) {
            return $this->_metadatas;
        }
        return is_array($this->_metadatas[$module_code]) ? $this->_metadatas[$module_code] : null;
    }

    /**
     * @param $module_code
     * @param $key
     * @return mixed
     */
    public function getMetadata($module_code, $key = null)
    {
        $metadatas = $this->getMetadatas($module_code);
        return $metadatas[$key];
    }

    /**
     * @param $module_code_or_datas
     * @param $datas_for_module_code
     * @return $this
     */
    public function setMetadatas($module_code_or_datas, $datas_for_module_code = null)
    {
        if (is_array($module_code_or_datas)) {
            $this->_metadatas = $module_code_or_datas;
        } else {
            $this->_metadatas[$module_code_or_datas] = $datas_for_module_code;
        }
        return $this;
    }

    /**
     * @param $module_code
     * @param $key
     * @param $value
     * @return Customer_Model_Customer
     */
    public function setMetadata($module_code, $key, $value)
    {
        $datas = $this->getMetadatas($module_code);
        $datas[$key] = $value;
        return $this->setMetadatas($module_code, $datas);
    }

    /**
     * @param $module_code
     * @param $key
     * @return Customer_Model_Customer
     */
    public function removeMetadata($module_code, $key)
    {
        $datas = $this->getMetadatas($module_code);
        unset($datas[$key]);
        return $this->setMetadatas($module_code, $datas);
    }

    /**
     * @param null $image
     * @return $this
     */
    public function saveImage ($image = null): self
    {
        // If the image starts with data: this means it's a new one, and we must save it!
        if (!empty($image) &&
            strpos($image, 'data:') === 0) {

            $formattedName = md5($this->getId());
            $imagePath = $this->getBaseImagePath() . '/' . $formattedName;

            // Create customer's folder
            if (!is_dir($imagePath)) {
                mkdir($imagePath, 0777, true);
            }

            // Store the picture on the server
            $imageName = uniqid('prfl', true) . '.jpg';
            $destPath = $imagePath . '/' . $imageName;
            $newavatar = base64_decode(str_replace(' ', '+', preg_replace('#^data:image/\w+;base64,#i', '', $image)));
            $file = fopen($destPath, 'wb');
            fwrite($file, $newavatar);
            fclose($file);

            // Resize the image
            Thumbnailer_CreateThumb::createThumbnail($destPath, $destPath, 256, 256, 'jpg', true);

            $oldImage = $this->getFullImagePath();

            // Set the image to the customer
            $newImagePath = '/' . $formattedName . '/' . $imageName;
            $this
                ->setImage($newImagePath)
                ->setIsCustomImage(1)
                ->save();
            $data['image'] = $newImagePath;
            $data['is_custom_image'] = 1;

            // Clean-up old file!
            if ($oldImage) {
                unlink($oldImage);
            }
        }

        return $this;
    }

    /**
     * The only entrypoint for the customer
     *
     * @return array
     */
    public static function getCurrent()
    {
        /**
         * @var $customer Customer_Model_Customer
         */
        $customer = self::_getSession()->getCustomer();

        $payload = [];
        $payload['is_logged_in'] = false;
        $payload['isLoggedIn'] = false;

        if ($customer->getId()) {
            $metadatas = $customer->getMetadatas();
            if (empty($metadatas)) {
                $metadatas = json_decode('{}'); // we really need a javascript object here
            }

            //hide stripe customer id for secure purpose
            if (isset($metadatas->stripe) &&
                $metadatas->stripe &&
                array_key_exists('customerId', $metadatas->stripe) &&
                $metadatas->stripe['customerId']) {
                unset($metadatas->stripe['customerId']);
            }

            try {
                $bdInt = (int) $customer->getBirthdate();
                if ($bdInt === 0) {
                    throw new \Siberian\Exception('Jump to empty');
                }
                $birthdate = new DateTime();
                $birthdate->setTimestamp($bdInt);
                $birthdateString = $birthdate->format('d/m/Y');
            } catch (\Exception $e) {
                $birthdateString = '';
            }

            $payload = [
                'id' => $customer->getId(),
                'civility' => $customer->getCivility(),
                'firstname' => $customer->getFirstname(),
                'lastname' => $customer->getLastname(),
                'nickname' => $customer->getNickname(),
                'birthdate' => $birthdateString,
                'mobile' => $customer->getMobile(),
                'intl_mobile' => $customer->getMobile(),
                'image' => $customer->getImage(),
                'email' => $customer->getEmail(),
                'show_in_social_gaming' => (bool)$customer->getShowInSocialGaming(),
                'is_custom_image' => (bool)$customer->getIsCustomImage(),
                'can_access_locked_features' => (bool)$customer->canAccessLockedFeatures(),
                'communication_agreement' => (bool)$customer->getCommunicationAgreement(),
                'token' => (string)Zend_Session::getId(),
                'metadatas' => $metadatas
            ];

            if (Siberian_CustomerInformation::isRegistered('stripe')) {
                $exporter_class = Siberian_CustomerInformation::getClass('stripe');
                if (class_exists($exporter_class) && method_exists($exporter_class, 'getInformation')) {
                    $tmp_class = new $exporter_class();
                    $info = $tmp_class->getInformation($customer->getId());
                    $payload['stripe'] = $info ? $info : [];
                }
            }

            $payload['is_logged_in'] = true;
            $payload['isLoggedIn'] = true;
        }

        return $payload;
    }

}
