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
}