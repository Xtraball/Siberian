<?php

namespace PaymentStripe\Model;

use PaymentMethod\Model\GatewayAbstract;
use PaymentMethod\Model\GatewayInterface;
use Siberian\Exception;

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

    /**
     * @param $paymentId
     * @return PaymentIntent|void|null
     * @throws Exception
     * @throws \Zend_Exception
     */
    public function getPaymentById($paymentId)
    {
        $paymentIntent = (new PaymentIntent())->find($paymentId);
        if (!$paymentIntent->getId()) {
            throw new Exception(p__("payment_method", "This payment id doesn't exists."));
        }

        return $paymentIntent;
    }

}