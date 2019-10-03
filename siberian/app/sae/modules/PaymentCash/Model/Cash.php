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
     * @var array
     */
    public static $paymentMethods = ["cash"];

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
}