<?php

/**
 * Class Payment_Model_Payment
 */
class Payment_Model_Payment extends Core_Model_Default
{
    /**
     * @var
     */
    protected $_type_payment;

    /**
     * @var array
     */
    protected static $_types = [];

    /**
     * @return array
     */
    public static function getTypes()
    {
        return self::$_types;
    }

    /**
     * @return array
     */
    public static function getAvailableMethods()
    {
        $methods = [];
        foreach (self::getTypes() as $paymentCode => $paymentType) {
            if (Payment_Model_Payment::isSetup($paymentCode)) {
                $methods[$paymentCode] = $paymentType;
            }
        }

        return $methods;
    }

    /**
     * @param $method
     * @return bool
     */
    public static function isSetup($method)
    {
        $excludeKeys = ['is_testing'];

        $providerName = new Api_Model_Provider();
        $providerName->find($method, 'code');
        $keys = $providerName->getKeys();

        foreach ($keys as $key) {
            if (in_array($key->getKey(), $excludeKeys)) {
                continue;
            }

            if (!$key->getValue()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $typeCustomer
     * @return null
     */
    protected function getType($typeCustomer)
    {
        if (!$this->_type_payment) {
            if (!empty(self::$_types[$typeCustomer])) {
                $class = 'Payment_Model_' . self::$_types[$typeCustomer];
                $this->_type_payment = new $class();
                $this->_type_payment->addData($this->getData());
            }
        }

        return !empty($this->_type_payment) ? $this->_type_payment : null;
    }

    /**
     * @param $paymentMethod
     * @return Payment_Model_Stripe|Payment_Model_Paypal
     */
    public static function getPaymentClass($paymentMethod)
    {
        if (isset(self::getTypes()[$paymentMethod])) {
            $className = 'Payment_Model_' . self::getTypes()[$paymentMethod];
            return new $className();
        }
        return false;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        if ($this->getPaymentMethod()) {
            return $this->getType($this->getPaymentMethod())->getCode();
        }
        return '';
    }

    /**
     * @param $params
     * @return array
     */
    public function getPaymentData($params)
    {
        if ($this->getType($params['payment_method'])) {
            return $this->getType($params['payment_method'])->getPaymentData($params['order']);
        }
        return [];
    }

    /**
     * @return array
     */
    public function cancel()
    {
        if ($this->getPaymentMethod()) {
            return $this->getType($this->getPaymentMethod())->cancel();
        }
        return [];
    }

    /**
     * @return array
     */
    public function success()
    {
        if ($this->getPaymentMethod()) {
            return $this->getType($this->getPaymentMethod())
                ->setData($this->getData())
                ->setOrder($this->getOrder())
                ->success();
        }
        return [];
    }

    /**
     * @return array
     */
    public function manageRecurring()
    {
        if ($this->getPaymentMethod()) {
            return $this->getType($this->getPaymentMethod())
                ->setData($this->getData())
                ->manageRecurring();
        }
        return [];
    }

    /**
     * @param $code
     * @param $classSuffix
     */
    public static function addPaymentType($code, $classSuffix)
    {
        if (!isset(self::$_types[$code])) {
            self::$_types[$code] = $classSuffix;
        }
    }
}
