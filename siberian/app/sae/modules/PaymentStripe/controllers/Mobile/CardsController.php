<?php

use Customer_Model_Customer as Customer;
use Siberian\Exception;
use Siberian\Json;

use PaymentStripe\Model\Application as PaymentStripeApplication;
use PaymentStripe\Model\Customer as PaymentStripeCustomer;
use PaymentStripe\Model\Currency as PaymentStripeCurrency;
use PaymentStripe\Model\PaymentMethod as PaymentStripePaymentMethod;
use PaymentStripe\Model\PaymentIntent as PaymentStripePaymentIntent;

use PaymentMethod\Model\Payment as PaymentMethodPayment;

use Stripe\SetupIntent;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;

/**
 * Class PaymentStripe_Mobile_CardsController
 */
class PaymentStripe_Mobile_CardsController extends Application_Controller_Mobile_Default
{
    /**
     * @throws Zend_Exception
     */
    public function savePaymentMethodAction()
    {
        try {
            $application = $this->getApplication();
            $request = $this->getRequest();
            $data = $request->getBodyParams();
            $customerId = $this->getSession()->getCustomerId();
            $customer = (new Customer())->find($customerId);

            if (!$customer->getId()) {
                throw new Exception(p__('payment_stripe',
                    'You session expired!'));
            }

            // Mobile app paymentMethod object!
            $paymentMethodPayload = $data['paymentMethod'];

            PaymentStripeApplication::init($application->getId());
            $stripeCustomer = PaymentStripeCustomer::getForCustomerId($customerId);

            // Attach the card (PaymentMethod) to the customer!
            $paymentMethod = PaymentMethod::retrieve($paymentMethodPayload['setupIntent']['payment_method']);
            $paymentMethod->attach(['customer' => $stripeCustomer->getToken()]);

            // Search for a similar card!
            $similarCards = (new PaymentStripePaymentMethod())->findAll([
                'exp = ?' => $paymentMethod['card']['exp_month'] . '/' . substr($paymentMethod['card']['exp_year'], 2),
                'last = ?' => $paymentMethod['card']['last4'],
                'brand LIKE ?' => $paymentMethod['card']['brand'],
                'stripe_customer_id = ?' => $stripeCustomer->getId(),
                'type = ?' => PaymentStripePaymentMethod::TYPE_CREDIT_CARD,
                'is_removed = ?' => '0',
            ]);

            if ($similarCards->count() > 0) {
                throw new Exception(p__(
                    'payment_stripe',
                    'Seems you already added this card! If the error persists, please remove the existing card first, then add it again.'));
            }

            $card = new PaymentStripePaymentMethod();
            $card
                ->setStripeCustomerId($stripeCustomer->getId())
                ->setType(PaymentStripePaymentMethod::TYPE_CREDIT_CARD)
                ->setBrand($paymentMethod['card']['brand'])
                ->setExp($paymentMethod['card']['exp_month'] . '/' . substr($paymentMethod['card']['exp_year'], 2))
                ->setLast($paymentMethod['card']['last4'])
                ->setPaymentMethod($paymentMethod['id'])
                ->setRawPayload(Json::encode($paymentMethod))
                ->setIsRemoved(0)
                ->save();

            /**
             * @var $clientCards PaymentStripePaymentMethod[]
             */
            $clientCards = (new PaymentStripePaymentMethod())
                ->fetchForStripeCustomerId($stripeCustomer->getId());
            $cards = [];
            foreach ($clientCards as $clientCard) {
                $cards[] = $clientCard->toJson();
            }

            $payload = [
                'success' => true,
                'lastCardId' => $card->getId(),
                'cards' => $cards
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
                'trace' => $e->getTrace()
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * @throws Zend_Controller_Response_Exception
     */
    public function fetchSetupIntentAction ()
    {
        try {
            $application = $this->getApplication();
            $session = $this->getSession();
            $customerId = $session->getCustomerId();

            PaymentStripeApplication::init($application->getId());

            // Here we create the customer in the Stripe account
            $stripeCustomer = PaymentStripeCustomer::getForCustomerId($customerId);

            $setupIntent = SetupIntent::create([
                'payment_method_types' => [
                    'card'
                ],
                'customer' => $stripeCustomer->getToken()
            ]);

            $payload = [
                'success' => true,
                'setupIntent' => $setupIntent
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function fetchPaymentIntentAction ()
    {
        try {
            $application = $this->getApplication();
            $appId = $application->getId();
            $session = $this->getSession();
            $customerId = $session->getCustomerId();
            $request = $this->getRequest();
            $data = $request->getBodyParams();
            $card = $data['card'];
            $options = $data['options'];
            $amount = $options['payment']['amount'];
            $currency = $options['payment']['currency'] ?? $application->getCurrency();

            $paymentMethod = (new PaymentStripePaymentMethod())->find($card['id']);

            if (!$paymentMethod || !$paymentMethod->getId()) {
                throw new \Siberian\Exception(p__('payment_stripe', 'This card no longer exists!'));
            }

            $paymentMethodToken = trim($paymentMethod->getToken());
            if (empty($paymentMethodToken)) {
                throw new \Siberian\Exception(p__('payment_stripe', 'This card no longer exists!'));
            }

            PaymentStripeApplication::init($appId);
            $stripeCustomer = PaymentStripeCustomer::getForCustomerId($customerId);

            if (!$stripeCustomer || !$stripeCustomer->getId()) {
                throw new \Siberian\Exception(p__('payment_stripe', 'There is an issue retrieving your card!'));
            }

            $stripeAmount = PaymentStripeCurrency::getAmountForCurrency($amount, $currency);
            $paymentIntent = PaymentIntent::create([
                'payment_method' => $paymentMethod->getToken(),
                'currency' => $currency,
                'confirmation_method' => 'automatic',
                'confirm' => true,
                'capture_method' => 'manual',
                'setup_future_usage' => 'off_session',
                'amount' => $stripeAmount,
                'customer' => $stripeCustomer->getToken()
            ]);

            $stripePaymentIntent = new PaymentStripePaymentIntent();
            $stripePaymentIntent
                ->setAppId($appId)
                ->setStripeCustomerId($stripeCustomer->getId())
                ->setData('token', $paymentIntent->id)
                ->setPmToken($paymentMethod->getToken())
                ->setPmId($paymentMethod->getId())
                ->setCurrency($currency)
                ->setConfirmationMethod('automatic')
                ->setCaptureMethod('manual')
                ->setSetupFutureUsage('off_session')
                ->setAmount($amount)
                ->setStripeAmount($stripeAmount)
                ->setCustomerId($customerId)
                ->setStripeCustomerToken($stripeCustomer->getToken())
                ->setStatus($paymentIntent['status'])
                ->save();

            // Attaching to a generic payment
            $payment = PaymentMethodPayment::createOrGetFromModal([
                'id' => $stripePaymentIntent->getId(),
                'code' => \PaymentStripe\Model\Stripe::$shortName
            ]);

            $payload = [
                'success' => true,
                'client_secret' => $paymentIntent['client_secret'],
                'pi_status' => $paymentIntent['status'],
                'pi_id' => $paymentIntent['id'],
                'paymentId' => (integer) $payment->getId()
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    public function fetchSettingsAction ()
    {
        try {
            $application = $this->getApplication();
            $settings = PaymentStripeApplication::getSettings($application->getId());

            $payload = [
                'success' => true,
                'settings' => $settings->_toJson(),
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function fetchVaultsAction ()
    {
        try {
            $session = $this->getSession();
            $customerId = $session->getCustomerId();

            $vaults = (new PaymentStripePaymentMethod())->getForCustomerId($customerId, [
                'payment_stripe_payment_method.is_removed = ?' => 0,
            ]);

            $dataVaults = [];
            foreach ($vaults as $vault) {
                $dataVaults[] = $vault->toJson();
            }

            $payload = [
                'success' => true,
                'vaults' => $dataVaults,
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function deletePaymentMethodAction()
    {
        try {
            $application = $this->getApplication();
            $request = $this->getRequest();
            $data = $request->getBodyParams();
            $card = $data['card'];

            $paymentMethod = (new PaymentStripePaymentMethod())->find($card['id']);
            if (!$paymentMethod || !$paymentMethod->getId()) {
                throw new Exception(p__('payment_stripe', "This payment method doesn't exists."));
            }

            $paymentIntent = (new PaymentStripePaymentIntent())->findAll([
                'pm_token = ?' => $paymentMethod->getToken(),
                'status = ?' => 'requires_capture'
            ]);

            if ($paymentIntent->count() > 0) {
                throw new Exception(p__('payment_stripe',
                    "This payment method can't be removed, it's linked to a pending payment."));
            }

            PaymentStripeApplication::init($application->getId());

            //$stripePaymentMethod = PaymentMethod::retrieve($paymentMethod->getToken());
            //$stripePaymentMethod->detach();

            $paymentMethod
                ->setIsRemoved(1)
                ->save();

            $payload = [
                'success' => true,
                'message' => p__('cabride', 'This payment method is now deleted!'),
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }
}
