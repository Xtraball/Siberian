<?php

namespace PaymentMethod\Model;

use Core\Model\Base;

/**
 * Class GatewayAbstract
 * @package PaymentMethod\Model
 */
abstract class GatewayAbstract extends Base
{
    /**
     * @var array
     */
    public static $paymentMethods = [];

    /**
     * @param $paymentMethod
     * @return bool
     */
    public function supports($paymentMethod)
    {
        return in_array($paymentMethod, static::$paymentMethods);
    }

    /**
     * @param null $appId
     * @return bool
     */
    public function isSetup($appId = null)
    {
        return false;
    }

    /**
     * @param $paymentId
     */
    public function getPaymentById($paymentId)
    {
        throw new Exception(p__("payment_method", "This payment id doesn't exists."));
    }
}