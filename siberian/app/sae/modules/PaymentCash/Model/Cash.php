<?php

namespace PaymentCash\Model;

use PaymentMethod\Model\GatewayAbstract;
use PaymentMethod\Model\GatewayInterface;

/**
 * Class Cash
 * @package PaymentStripe\Model
 */
class Cash
    extends GatewayAbstract
    implements GatewayInterface
{
    /**
     * @var string
     */
    public static $paymentMethod = "cash";

    /**
     * @var string
     */
    public static $shortName = "cash";

    /**
     * @param null $appId
     * @return bool
     */
    public function isSetup($appId = null)
    {
        return Application::isEnabled($appId);
    }

    public function authorizationSuccess()
    {

    }

    public function authorizationError()
    {

    }

    public function captureSuccess()
    {

    }

    public function captureError()
    {

    }

    public function paymentSuccess()
    {

    }

    public function paymentError()
    {

    }

    /**
     * @param $paymentId
     * @return Cash|void
     * @throws \Zend_Exception
     */
    public function getPaymentById($paymentId)
    {
        $instance = new static();
        $instance->setPaymentMethodId($paymentId);

        return $instance;
    }
}