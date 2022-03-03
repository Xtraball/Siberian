<?php

namespace PaymentMethod\Model;

use Core\Model\Base;
use Siberian\Exception;

/**
 * Class GatewayAbstract
 * @package PaymentMethod\Model
 */
abstract class GatewayAbstract extends Base
{
    /**
     * @var string
     */
    public static $paymentMethod = "";

    /**
     * @var string
     */
    public static $shortName = "";

    /**
     * @param $paymentMethod
     * @return bool
     */
    public function supports($paymentMethod): bool
    {
        return mb_strtolower($paymentMethod) === mb_strtolower(static::$paymentMethod);
    }

    /**
     * @param null $appId
     * @return bool
     */
    public function isSetup($appId = null): bool
    {
        return false;
    }

    /**
     * @param $paymentId
     * @throws Exception
     * @throws \Zend_Exception
     */
    public function getPaymentById($paymentId)
    {
        throw new Exception(p__("payment_method", "This payment id doesn't exists."));
    }
}