<?php

/**
 * Class Core_Model_Session
 */
class Core_Model_Session extends Zend_Session_Namespace
{

    /**
     *
     */
    const TYPE_ADMIN = 'front';
    /**
     *
     */
    const TYPE_BACKOFFICE = 'backoffice';
    /**
     *
     */
    const TYPE_CUSTOMER = 'customer';
    /**
     *
     */
    const TYPE_MCOMMERCE = 'mcommerce';

    /**
     * @var array
     */
    protected $_types = [];
    /**
     * @var array
     */
    protected $_instanceSingleton = [];
    /**
     * @var null
     */
    protected $_store = null;
    /**
     * @var null
     */
    protected $_application = null;
    /**
     * @var null
     */
    protected $_cart = null;

    /**
     * @return array
     */
    public function getTypes(): array
    {
        return array(
            self::TYPE_ADMIN => 'Admin_Model_Session',
            self::TYPE_BACKOFFICE => 'Backoffice_Model_Session',
            self::TYPE_CUSTOMER => 'Customer_Model_Session',
            self::TYPE_MCOMMERCE => 'Core_Model_Default',
        );
    }

    /**
     * @return $this
     */
    public function resetInstance(): self
    {
        $this->_instanceSingleton = [];
        $this->current_type = null;
        $this->object_id = null;
        return $this;
    }

    /**
     * @return bool|mixed
     * @throws Exception
     */
    public function getInstance()
    {
        if (!array_key_exists($this->current_type, $this->getTypes())) {
            return false;
        }

        if (!isset($this->_instanceSingleton[$this->current_type])) {
            $params['id'] = $this->object_id;
            $class = $this->_getClassFor($this->current_type);
            $instance = new $class($params);
            if (!$instance->getObject()->getId()) {
                $this->object_id = null;
            }
            $this->_instanceSingleton[$this->current_type] = $instance;
        }

        return $this->_instanceSingleton[$this->current_type];
    }

    /**
     * @param $currentType
     * @return $this
     */
    public function setCurrentType($currentType): self
    {
        $this->current_type = $currentType;
        return $this;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getAccountUri()
    {
        if ($this->getInstance()) {
            return $this->getInstance()->getAccountUri();
        }
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getLogoutUri()
    {
        if ($this->getInstance()) {
            return $this->getInstance()->getLogoutUri();
        }
    }

    /**
     * @param null $type
     * @return bool
     * @throws Exception
     */
    public function isLoggedIn($type = null): bool
    {
        if ((
                $type === null ||
                $type == $this->loggedAs()
            ) &&
            $this->getInstance()) {
            return $this->getInstance()->isLoggedIn();
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function loggedAs()
    {
        return $this->current_type;
    }

    /**
     * @param $msg
     * @param string $key
     * @return $this
     */
    public function addSuccess($msg, $key = '')
    {
        $messages = $this->getMessages(false);
        $messages->addSuccess($msg, $key);
        $this->messages = $messages;
        return $this;
    }

    /**
     * @param $msg
     * @param string $key
     * @return $this
     */
    public function addWarning($msg, $key = '')
    {
        $messages = $this->getMessages(false);
        $messages->addWarning($msg, $key);
        $this->messages = $messages;
        return $this;
    }

    /**
     * @param $key
     * @return $this
     */
    public function removeWarning($key)
    {
        $messages = $this->getMessages(false);
        $messages->removeWarning($key);
        $this->messages = $messages;
        return $this;
    }

    /**
     * @param $msg
     * @param string $key
     * @return $this
     */
    public function addError($msg, $key = '')
    {
        $messages = $this->getMessages(false);
        $messages->addError($msg, $key);
        $this->messages = $messages;
        return $this;
    }

    /**
     * @param bool $reset
     * @return Core_Model_Session_Messages|mixed
     */
    public function getMessages($reset = true)
    {
        if (!$this->messages instanceof Core_Model_Session_Messages) {
            $this->messages = new Core_Model_Session_Messages();
        }
        $messages = $this->messages;
        if ($reset) {
            $this->messages = null;
        }
        return $messages;
    }

    /**
     * @return Mcommerce_Model_Store|null
     */
    public function getStore()
    {
        if (!$this->_store) {
            $this->_store = new Mcommerce_Model_Store();
            if ($this->store_id) {
                $this->_store->find($this->store_id);
            }
        }
        return $this->_store;
    }

    /**
     * @param $store
     * @return $this
     */
    public function setStore($store)
    {
        $this->store_id = $store->getId();
        return $this;
    }

    /**
     * @return Mcommerce_Model_Cart|null
     */
    public function getCart()
    {
        if (!$this->_cart) {
            $this->_cart = new Mcommerce_Model_Cart();
            if ($this->cart_id) {
                $this->_cart->find($this->cart_id);
            }
        }
        return $this->_cart;
    }

    /**
     * @param $cart
     * @return $this
     */
    public function setCart($cart)
    {
        $this->cart_id = $cart->getId();
        return $this;
    }

    /**
     * @return $this
     */
    public function unsetCart()
    {
        $this->cart_id = null;
        return $this;
    }

    /**
     * @return Customer_Model_Customer
     */
    public function getCustomer()
    {
        if ($this->getInstance() AND $this->loggedAs() == self::TYPE_CUSTOMER) {
            return $this->getInstance()->getObject();
        }
        return new Customer_Model_Customer();
    }

    /**
     * @param $customer
     * @return mixed
     */
    public function setCustomer($customer)
    {
        $this->setCurrentType(self::TYPE_CUSTOMER)
            ->setCustomerId($customer->getId());
        return $this->getInstance()->setObject($customer);
    }

    /**
     * @return mixed|null
     */
    public function getCustomerId()
    {
        $id = null;
        if ($this->loggedAs() == self::TYPE_CUSTOMER) {
            $id = $this->object_id;
        }
        return $id;
    }

    /**
     * @param $id
     */
    public function setCustomerId($id)
    {
        $this->object_id = $id;
    }

    /**
     * @return Admin_Model_Admin
     * @throws Zend_Exception
     */
    public function getAdmin()
    {
        if ($this->getInstance() AND $this->loggedAs() == self::TYPE_ADMIN) {
            return $this->getInstance()->getObject();
        }
        return new Admin_Model_Admin();
    }

    /**
     * @param $admin
     * @return $this
     */
    public function setAdmin($admin)
    {
        $this->setCurrentType(self::TYPE_ADMIN)
            ->setAdminId($admin->getId());
        $this->getInstance()->setObject($admin);
        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getAdminId()
    {
        $id = null;
        if ($this->loggedAs() == self::TYPE_ADMIN) {
            $id = $this->object_id;
        }
        return $id;
    }

    /**
     * @param $id
     */
    public function setAdminId($id)
    {
        $this->object_id = $id;
    }

    /**
     * @return Backoffice_Model_Backoffice
     */
    public function getBackofficeUser()
    {
        if ($this->getInstance() AND ($this->loggedAs() == self::TYPE_BACKOFFICE)) {
            return $this->getInstance()->getObject();
        }
        return new Backoffice_Model_Backoffice();
    }

    /**
     * @param $user
     * @return $this
     */
    public function setBackofficeUser($user)
    {
        $this->resetInstance()
            ->setCurrentType(self::TYPE_BACKOFFICE)
            ->setBackofficeUserId($user->getId());
        $this->getInstance()->setObject($user);
        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getBackofficeUserId()
    {
        $id = null;
        if ($this->loggedAs() == self::TYPE_BACKOFFICE) {
            $id = $this->object_id;
        }
        return $id;
    }

    /**
     * @param $id
     */
    public function setBackofficeUserId($id)
    {
        $this->object_id = $id;
    }

    /**
     * @return Application_Model_Application|null
     */
    public function getApplication()
    {
        if ($this->getAppId()) {
            if (!$this->_application) {
                $this->_application = new Application_Model_Application();
                $this->_application->find($this->getAppId());
            }
            return $this->_application;
        }
        return new Application_Model_Application();
    }

    /**
     * @param $id
     * @return $this
     */
    public function setAppId($id)
    {
        $this->application_id = $id;
        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getAppId()
    {
        $app_id = null;
        if (in_array($this->loggedAs(), array(self::TYPE_ADMIN, self::TYPE_CUSTOMER))) {
            $app_id = $this->application_id;
        }
        return $app_id;
    }

    /**
     *
     */
    public function unsetAppId()
    {
        $this->setAppId(null);
    }

    /**
     * @param $type
     * @return mixed
     * @throws Exception
     */
    protected function _getClassFor($type)
    {
        $types = $this->getTypes();
        if (!array_key_exists($type, $types)) {
            throw new Exception("An general error occurred. Please, try again later.");
        }

        return $types[$type];
    }
}
