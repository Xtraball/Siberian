<?php

namespace PaymentMethod\Model;

use Core\Model\Base;

/**
 * Class Payment
 * @package PaymentMethod\Model
 */
class Payment extends Base
{
    /**
     * Customer constructor.
     * @param array $params
     * @throws \Zend_Exception
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = 'PaymentMethod\Model\Db\Table\Payment';
    }

    /**
     * @return GatewayAbstract|null
     */
    public function retrieve ()
    {
        $class = $this->getMethodClass();
        $id = $this->getMethodId();

        if (class_exists($class)) {
            return (new $class())->getPaymentById($id);
        }

        return null;
    }

    /**
     * @param $paymentId
     * @return Payment|null
     * @throws \Zend_Exception
     */
    static public function createFromModal($paymentId)
    {
        if (is_array($paymentId)) {
            $instance = new self();
            $instance
                ->setMethodId($paymentId["id"])
                ->setMethodClass($paymentId["method"])
                ->save();
        } else {
            $instance = (new self())->find($paymentId);
        }

        return $instance;
    }

    /**
     * @param $methodClass
     * @return Payment
     */
    public function setMethodClass($methodClass)
    {
        return $this->setData("method_class", base64_encode($methodClass));
    }

    /**
     * @return bool|string
     */
    public function getMethodClass()
    {
        return base64_decode($this->getData("method_class"));
    }
}