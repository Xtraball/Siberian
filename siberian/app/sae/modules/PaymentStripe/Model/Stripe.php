<?php

namespace PaymentStripe\Model;

use PaymentMethod\Model\GatewayAbstract;
use PaymentMethod\Model\GatewayInterface;
use PaymentStripe\Model\Application as PaymentStripeApplication;
use Siberian\Exception;
use Siberian\Json;
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
     * @param null $appId
     * @return bool
     */
    public static function sIsSetup($appId = null): bool
    {
        return Application::isEnabled($appId);
    }

    /**
     * @param Customer $stripeCustomer
     * @param array $params
     */
    public function authorize(Customer $stripeCustomer, array $params)
    {

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
     * @throws Exception
     * @throws \Zend_Exception
     */
    public function capture($intent = null, $params = [])
    {
        try {
            $appId = $intent->getAppId();

            // Init stripe
            PaymentStripeApplication::init($appId);

            $paymentIntent = StripePaymentIntent::retrieve($intent->getData('token'));
            $paymentIntent->capture($params);

            // Update DB intent*
            $intent
                ->setStatus($paymentIntent->status)
                ->save();

        } catch (\Exception $e) {
            $log = new Log();
            $log
                ->setMessage($e->getMessage())
                ->setRawPayload(Json::encode($e->getTrace()))
                ->save();

            throw new \Siberian\Exception(p__('payment_stripe', 'Something went wrong while capturing the payment intent'));
        }
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