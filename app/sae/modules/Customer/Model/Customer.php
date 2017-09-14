<?php

class Customer_Model_Customer extends Core_Model_Default
{

    const IMAGE_PATH = '/images/customer';

    protected $_social_datas = array();
    protected $_types = array('facebook');
    protected $_social_instances = array();

    public function __construct($datas = array()) {
        parent::__construct($datas);
        $this->_db_table = 'Customer_Model_Db_Table_Customer';
    }

    /**
     * @param $app_id
     * @return mixed
     */
    public function findByAppId($app_id) {
        return $this->getTable()->findByAppId($app_id);
    }

    public function findByEmail($email) {
        return $this->find($email, 'email');
    }

    public function findBySocialId($id, $type, $app_id) {
        $datas = $this->getTable()->findBySocialId($id, $type, $app_id);
        if (!empty($datas)) {
            $this->setData($datas)
                ->setId($datas['customer_id']);
        }

        return $this;
    }

    public function findAllCustomersByApp($values = null, $params = null) {
        return $this->getTable()->findAllCustomersByApp($values, $params);
    }

    public function getName() {
        return $this->getFirstname() . ' ' . $this->getLastname();
    }

    public function getFacebook() {
        return $this->getSocialObject('facebook');
    }

    public function authenticate($password) {
        return $this->_checkPassword($password, $this->getPassword());
    }

    public function canAccessLockedFeatures() {
        return $this->getData("can_access_locked_features") || $this->getApplication()->getAllowAllCustomersToAccessLockedFeatures();
    }

    public function getSocialObject($name, $params = array()) {

        if (empty($this->_social_instances[$name])) {
            if (empty($params)) $params = $this->getSocialDatas($name);
            if (in_array($name, $this->_types)) {
                $social_datas = !empty($params['datas']) ? $params['datas'] : array();
                $class = 'Customer_Model_Customer_Type_' . ucfirst($name);
                $this->_social_instances[$name] = new $class(array('social_id' => $params['social_id'], 'social_datas' => $social_datas, 'application' => $this->getApplication()));
                $this->_social_instances[$name]->setCustomer($this);
            }
        }

        return !empty($this->_social_instances[$name]) ? $this->_social_instances[$name] : null;
    }

    public function getSocialDatas($name = null) {
        if (!$this->getId()) return null;
        if (is_null($name)) return $this->_social_datas;
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
    public function setSocialDatas(array $datas) {
        $this->_social_datas = $datas;
        return $this;
    }

    /**
     *
     * @param string $type 'facebook', etc...
     * @param array $datas => ('id' => $id, 'data' => $data);
     * @return Customer_Model_Customer
     */
    public function setSocialData($type, array $datas) {
        $this->_social_datas[$type] = $datas;
        return $this;
    }

    public function canPostSocialMessage() {

        foreach ($this->_types as $type) {
            if ($this->getSocialObject($type)->isValid()) return true;
        }
        return false;

    }

    public function addSocialPost($customer_message, $message_type, $points = null) {
        if ($this->canPostSocialMessage()) {
            $this->getTable()->addSocialPost($this->getId(), $customer_message, $message_type, $points);
        }
        return $this;
    }

    public function deleteSocialPost($post_id) {
        $this->getTable()->deleteSocialPost($this->getId(), $post_id);
        return $this;
    }

    public function postSocialMessage($pos, $datas) {

        $isOk = true;

        foreach ($this->_types as $type) {
            if ($social = $this->getSocialObject($type) AND $social->isValid()) {

                $message = $social->prepareMessage($datas['message_type'], $pos, $datas['points']);
                $customer_message = !empty($datas['customer_message']) ? $datas['customer_message'] : "";
                $social->postMessage($pos, $customer_message, $message);
            }
        }

        return $isOk;
    }

    public function findAllPosts() {
        return $this->getTable()->findAllPosts();
    }

    public function findAllWithDeviceUid($app_id = null) {
        return $this->getTable()->findAllWithDeviceUid($app_id);
    }

    public function isSamePassword($password) {
        return $this->getPassword() == $this->_encrypt($password);
    }

    public function setPassword($password) {
        if (strlen($password) < 6) throw new Exception($this->_('The password must be at least 6 characters'));
        $this->setData('password', $this->_encrypt($password));
        return $this;
    }

    public function getImagePath() {
        return Core_Model_Directory::getPathTo(self::IMAGE_PATH);
    }

    public function getBaseImagePath() {
        return Core_Model_Directory::getBasePathTo(self::IMAGE_PATH);
    }

    public function getImageLink() {
        if ($this->getData('image') AND is_file($this->getBaseImagePath() . '/' . $this->getImage())) return $this->getImagePath() . '/' . $this->getImage();
        else return $this->getNoImage();
    }

    public function getFullImagePath() {
        if ($this->getData('image') AND is_file($this->getBaseImagePath() . '/' . $this->getImage())) return $this->getBaseImagePath() . '/' . $this->getImage();
        return null;
    }

    //
    public function getNoImage() {
        return $this->getImagePath() . '/placeholder/no-image.png';
    }

    public function save($sanityCheck = true) {
        parent::save();
        if (!is_null($this->_social_datas)) {
            $datas = array();
            foreach ($this->_social_datas as $type => $data) {
                $datas[] = array('type' => $type, 'social_id' => $data['id'], 'datas' => serialize(!empty($data['datas']) ? $data['datas'] : array()));
            }
            $this->getTable()->insertSocialDatas($this->getId(), $datas, $this->getApplication()->getId());
        }
        if (is_array($this->_metadatas) && !empty($this->_metadatas)) {
            $datas = array();
            foreach ($this->_metadatas as $module_code => $data) {
                $datas[] = array('code' => $module_code, 'datas' => serialize(!empty($data) ? $data : null));
            }
            $this->getTable()->insertMetadatas($this->getId(), $datas);
        }
    }

    private function _checkPassword($password, $hash) {
        return $this->_encrypt($password) == $hash;
    }

    private function _encrypt($password) {
        return sha1($password);
    }

    public function getAppIdByCustomerId() {
        return $this->getTable()->getAppIdByCustomerId();
    }

    /**
     * @param null $module_code
     * @return null
     */
    public function getMetadatas($module_code = null) {
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

    public function getMetadata($module_code, $key) {
        $metadatas = $this->getMetadatas($module_code);
        return $metadatas[$key];
    }

    public function setMetadatas($module_code_or_datas, $datas_for_module_code) {
        if (is_array($module_code_or_datas)) {
            $this->_metadatas = $module_code_or_datas;
        } else {
            $this->_metadatas[$module_code_or_datas] = $datas_for_module_code;
        }
        return $this;
    }

    public function setMetadata($module_code, $key, $value) {
        $datas = $this->getMetadatas($module_code);
        $datas[$key] = $value;
        return $this->setMetadatas($module_code, $datas);
    }

    public function removeMetadata($module_code, $key) {
        $datas = $this->getMetadatas($module_code);
        unset($datas[$key]);
        return $this->setMetadatas($module_code, $datas);
    }

    /**
     * The only entrypoint for the customer
     *
     * @return array
     */
    public static function getCurrent() {
        $customer = self::_getSession()->getCustomer();

        $payload = array();
        $payload["is_logged_in"] = false;

        if($customer->getId()) {
            $metadatas = $customer->getMetadatas();
            if(empty($metadatas)) {
                $metadatas = json_decode("{}"); // we really need a javascript object here
            }

            //hide stripe customer id for secure purpose
            if($metadatas->stripe && array_key_exists("customerId",$metadatas->stripe) && $metadatas->stripe["customerId"]) {
                unset($metadatas->stripe["customerId"]);
            }

            $payload = array(
                "id"                            => $customer->getId(),
                "civility"                      => $customer->getCivility(),
                "firstname"                     => $customer->getFirstname(),
                "lastname"                      => $customer->getLastname(),
                "nickname"                      => $customer->getNickname(),
                "email"                         => $customer->getEmail(),
                "show_in_social_gaming"         => (bool) $customer->getShowInSocialGaming(),
                "is_custom_image"               => (bool) $customer->getIsCustomImage(),
                "can_access_locked_features"    => (bool) $customer->canAccessLockedFeatures(),
                "metadatas"                     => $metadatas
            );

            if(Siberian_CustomerInformation::isRegistered("stripe")) {
                $exporter_class = Siberian_CustomerInformation::getClass("stripe");
                if(class_exists($exporter_class) && method_exists($exporter_class, "getInformation")) {
                    $tmp_class = new $exporter_class();
                    $info = $tmp_class->getInformation($customer->getId());
                    $payload["stripe"] = $info ? $info : array();
                }
            }

            $payload["is_logged_in"] = true;

        }

        return $payload;
    }

}
