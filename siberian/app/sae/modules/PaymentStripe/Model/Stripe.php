<?php

namespace PaymentStripe\Model;

use PaymentMethod\Model\GatewayAbstract;
use PaymentMethod\Model\GatewayInterface;
use Siberian\Exception;
use Stripe\PaymentIntent as StripePaymentIntent;

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
    public static $paymentMethod = 'credit-card';

    /**
     * @var string
     */
    public static $shortName = 'stripe';

    /**
     * @param null $appId
     * @return bool
     */
    public function isSetup($appId = null): bool
    {
        return Application::isEnabled($appId);
    }

    /**
     * @param Customer $stripeCustomer
     * @param array $params
     * @return PaymentIntent
     * @throws Exception
     * @throws \Zend_Exception
     */
    public function authorize(Customer $stripeCustomer, array $params): PaymentIntent
    {
        $stripePaymentIntent = StripePaymentIntent::create($params);

        $paymentIntent = new PaymentIntent();
        $paymentIntent
            ->setStripeCustomerId($stripeCustomer->getId())
            ->setToken($stripePaymentIntent['id'])
            ->setStatus($stripePaymentIntent['status'])
            ->setIsRemoved(0)
            ->save();

        if (!in_array($stripePaymentIntent['status'], ['requires_capture', 'requires_action']) ) {
            throw new Exception(p__('cabride',
                "The payment authorization was declined, '%s'",
                $stripePaymentIntent['status']));
        }

        return $paymentIntent;
    }

    public function authorizationSuccess()
    {

    }

    public function authorizationError()
    {

    }

    public function capture()
    {

    }

    public function captureSuccess()
    {

    }

    public function captureError()
    {

    }

    public function pay()
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
        if ($paymentIntent && !$paymentIntent->getId()) {
            throw new Exception(p__('payment_method', "This payment id doesn't exists."));
        }

        return $paymentIntent;
    }

}