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
     * @var string
     */
    protected $_db_table = Db\Table\Payment::class;

    /**
     * @return GatewayAbstract|null
     */
    public function retrieve ()
    {
        try {
            $code = $this->getMethodCode();
            $id = $this->getMethodId();

            $gateway = Gateway::get($code);
            $gateway->getPaymentById($id);
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    /**
     * @param $paymentId
     * @return Payment|null
     * @throws \Zend_Exception
     */
    public static function createOrGetFromModal($paymentId)
    {
        if (is_array($paymentId)) {
            $instance = new self();
            $instance
                ->setMethodCode($paymentId['code'])
                ->setMethodId($paymentId['id'])
                ->save();
        } else {
            $instance = (new self())->find($paymentId);
        }

        return $instance;
    }

}