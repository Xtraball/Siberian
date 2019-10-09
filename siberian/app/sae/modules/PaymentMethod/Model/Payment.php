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
        return $this;
    }

    public function retrieve ()
    {
        $class = $this->getMethodClass();
        $id = $this->getMethodId();

        if (class_exists($class)) {
            $paymentInstance = (new $class())->getPaymentById($id);
        }
    }
}