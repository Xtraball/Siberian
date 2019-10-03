<?php

namespace PaymentStripe\Model;

use PaymentMethod\Model\GatewayAbstract;
use PaymentMethod\Model\GatewayInterface;

/**
 * Class Stripe
 * @package PaymentStripe\Model
 */
class Stripe
    extends GatewayAbstract
    implements GatewayInterface
{
    /**
     * @var array
     */
    public static $paymentMethods = ["credit-card"];

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