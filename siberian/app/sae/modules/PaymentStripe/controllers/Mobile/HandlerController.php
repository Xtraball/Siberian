<?php

use Customer_Model_Customer as Customer;
use PaymentMethod\Controller\AbstractMobilePaymentController;
use PaymentMethod\Model\Payment;
use PaymentStripe\Model\Application as PaymentStripeApplication;
use PaymentStripe\Model\Customer as PaymentStripeCustomer;
use PaymentStripe\Model\PaymentMethod as PaymentStripePaymentMethod;
use PaymentStripe\Model\PaymentIntent as PaymentStripePaymentIntent;
use Siberian\Exception;
use Siberian\Json;
use Stripe\PaymentMethod;
use Stripe\PaymentIntent;



/**
 * Class PaymentStripe_Mobile_HandlerController
 */
class PaymentStripe_Mobile_HandlerController
    extends AbstractMobilePaymentController
{
    public function authorizationSuccessAction()
    {
        // Saving the new payment method (credit-card), that's all!
        // Stripe add/remove cards is standalone!
        try {
            $application = $this->getApplication();
            $request = $this->getRequest();
            $session = $this->getSession();
            $data = $request->getBodyParams();
            $customerId = $session->getCustomerId();
            $customer = (new Customer())->find($customerId);

            if (!$customer->getId()) {
                throw new Exception(p__("payment_stripe",
                    "Your session expired!"));
            }

            // Mobile app paymentMethod object!
            $paymentMethodPayload = $data["payload"];

            PaymentStripeApplication::init($application->getId());

            // Attach the card (PaymentMethod) to the customer!
            $paymentIntent = PaymentIntent::retrieve($paymentMethodPayload["paymentIntent"]["id"]);
            $paymentIntent->confirm();

            $payload = [
                "success" => true,
                "paymentIntent" => $paymentIntent
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
                "trace" => $e->getTrace()
            ];
        }

        $this->_sendJson($payload);
    }

    public function authorizationErrorAction()
    {
        $this->__debug();
    }

    public function captureSuccessAction()
    {
        $this->__debug();
    }

    public function captureErrorAction()
    {
        $this->__debug();
    }

    public function paymentSuccessAction()
    {
        $this->__debug();
    }

    public function paymentErrorAction()
    {
        $this->__debug();
    }

    public function setupSuccessAction()
    {
        // Saving the new payment method (credit-card), that's all!
        // Stripe add/remove cards is standalone!
        try {
            $application = $this->getApplication();
            $request = $this->getRequest();
            $session = $this->getSession();
            $data = $request->getBodyParams();
            $customerId = $session->getCustomerId();
            $customer = (new Customer())->find($customerId);

            if (!$customer->getId()) {
                throw new Exception(p__("payment_stripe",
                    "Your session expired!"));
            }

            // Mobile app paymentMethod object!
            $paymentMethodPayload = $data["payload"];

            PaymentStripeApplication::init($application->getId());
            $stripeCustomer = PaymentStripeCustomer::getForCustomerId($customerId);

            // Attach the card (PaymentMethod) to the customer!
            $paymentMethod = PaymentMethod::retrieve($paymentMethodPayload["setupIntent"]["payment_method"]);
            $paymentMethod->attach(["customer" => $stripeCustomer->getToken()]);

            // Search for a similar card!
            $similarCards = (new PaymentStripePaymentMethod())->findAll([
                "exp = ?" => $paymentMethod["card"]["exp_month"] . "/" . substr($paymentMethod["card"]["exp_year"], 2),
                "last = ?" => $paymentMethod["card"]["last4"],
                "brand LIKE ?" => $paymentMethod["card"]["brand"],
                "stripe_customer_id = ?" => $stripeCustomer->getId(),
                "type = ?" => PaymentStripePaymentMethod::TYPE_CREDIT_CARD,
                "is_removed = ?" => "0",
            ]);

            if ($similarCards->count() > 0) {
                throw new Exception(p__(
                    "payment_stripe",
                    "Seems you already added this card! If the error persists, please remove the existing card first, then add it again."));
            }

            $card = new PaymentStripePaymentMethod();
            $card
                ->setStripeCustomerId($stripeCustomer->getId())
                ->setType(PaymentStripePaymentMethod::TYPE_CREDIT_CARD)
                ->setBrand($paymentMethod["card"]["brand"])
                ->setExp($paymentMethod["card"]["exp_month"] . "/" . substr($paymentMethod["card"]["exp_year"], 2))
                ->setLast($paymentMethod["card"]["last4"])
                ->setToken($paymentMethod["id"])
                ->setRawPayload(Json::encode($paymentMethod))
                ->setIsRemoved(0)
                ->save();

            /**
             * @var $clientCards PaymentStripePaymentMethod[]
             */
            $clientCards = (new PaymentStripePaymentMethod())
                ->getForCustomerId($customerId);
            $cards = [];
            foreach ($clientCards as $clientCard) {
                $cards[] = $clientCard->toJson();
            }

            $payload = [
                "success" => true,
                "lastCardId" => $card->getId(),
                "cards" => $cards
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
                "trace" => $e->getTrace()
            ];
        }

        $this->_sendJson($payload);
    }

    public function setupErrorAction()
    {
        $this->__debug();
    }

    private function __debug()
    {
        try {
            $this->_sendJson([
                "success" => true,
                "params" => $this->getRequest()->getBodyParams(),
            ]);
        } catch (\Exception $e) {
            $this->_sendJson([
                "error" => true,
                "message" => $e->getMessage(),
            ]);
        }
    }
}
