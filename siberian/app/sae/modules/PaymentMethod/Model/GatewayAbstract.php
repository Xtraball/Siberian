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
     * @var string
     */
    public static $paymentMethod = "-";

    /**
     * @param $paymentMethod
     * @return bool
     */
    public function supports($paymentMethod)
    {
        return mb_strtolower($paymentMethod) === mb_strtolower(self::$paymentMethod);
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