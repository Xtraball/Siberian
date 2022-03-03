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
    public static $paymentMethod = 'cash';

    /**
     * @var string
     */
    public static $shortName = 'cash';

    /**
     * @param null $appId
     * @return bool
     */
    public function isSetup($appId = null): bool
    {
        return Application::isEnabled($appId);
    }

    public function authorizationSuccess()
    {

    }

    public function authorizationError()
    {

    }

    /**
     * @param null $intent
     * @param array $params
     */
    public function capture($intent = null, $params = [])
    {
        // Placeholder method to match siblings*
        // It's cash, so there is nothing to capture.
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