<?php

use Customer_Model_Customer as Customer;
use Siberian\Exception;
use Siberian\Json;

use PaymentStripe\Model\Application as PaymentStripeApplication;
use PaymentStripe\Model\Customer as PaymentStripeCustomer;
use PaymentStripe\Model\PaymentMethod as PaymentStripePaymentMethod;

use Stripe\Stripe;
use Stripe\SetupIntent;
use Stripe\PaymentMethod;
use Stripe\Customer as StripeCustomer;
use Stripe\Error\InvalidRequest;

use Cabride\Model\Client;

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
                throw new Exception(p__("payment_stripe",
                    "You session expired!"));
            }

            // Mobile app paymentMethod object!
            $paymentMethodPayload = $data["paymentMethod"];

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
                ->setPaymentMethod($paymentMethod["id"])
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

    /**
     *
     */
    public function fetchSetupIntentAction ()
    {
        try {
            $application = $this->getApplication();
            $session = $this->getSession();
            $customerId = $session->getCustomerId();

            PaymentStripeApplication::init($application->getId());
            $stripeCustomer = PaymentStripeCustomer::getForCustomerId($customerId);

            $setupIntent = SetupIntent::create([
                "payment_method_types" => ["card"],
                "customer" => $stripeCustomer->getToken()
            ]);

            $payload = [
                "success" => true,
                "setupIntent" => $setupIntent
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage()
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
                "success" => true,
                "settings" => $settings->toJson(),
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    public function fetchVaultsAction ()
    {
        try {
            $session = $this->getSession();
            $customerId = $session->getCustomerId();

            $vaults = (new PaymentStripePaymentMethod())->getForCustomerId($customerId, [
                "payment_stripe_payment_method.is_removed = ?" => 0,
            ]);

            $dataVaults = [];
            foreach ($vaults as $vault) {
                $dataVaults[] = $vault->toJson();
            }

            $payload = [
                "success" => true,
                "vaults" => $dataVaults,
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * @throws Zend_Exception
     */
    public function deleteVaultAction()
    {
        $optionValue = $this->getCurrentOptionValue();
        $cabride = (new Cabride())->find($optionValue->getId(), "value_id");

        switch ($cabride->getPaymentProvider()) {
            case "stripe":
                $this->deleteVaultStripe();
                break;
        }
    }

    public function deleteVaultStripe()
    {
        try {
            $application = $this->getApplication();
            $request = $this->getRequest();
            $vaultId = $request->getParam("vaultId", null);
            $optionValue = $this->getCurrentOptionValue();
            $customerId = $this->getSession()->getCustomerId();

            $client = (new Client())->find($customerId, "customer_id");
            $cabride = (new Cabride())->find($optionValue->getId(), "value_id");
            $vault = (new ClientVault())->find([
                "client_vault_id" => $vaultId,
                "payment_provider" => "stripe",
            ]);

            if (!$vault->getId()) {
                throw new Exception(p__("cabride",
                    "This vault doesn't exists!"));
            }

            // Check if the vault can be safely removed!
            $requests = (new Request())->findAll([
                "client_vault_id = ?" => $vaultId,
                "status IN (?)" => ["pending", "accepted", "onway", "inprogress"]
            ]);

            if ($requests->count() > 0) {
                throw new Exception(p__("cabride",
                    "This vault can't be removed yet, it is currently used for a ride!"));
            }

            Stripe::setApiKey($cabride->getStripeSecretKey());

            // Delete the card from the Stripe customer!
            $paymentMethod = PaymentMethod::retrieve($vault->getPaymentMethod());
            $paymentMethod->detach();

            // "remove" the vault, we keep tack of it for recap pages & stripe search history!
            // We previously used the key `is_deleted`, but there is flow with ->save() which delete the record...
            $vault
                ->setIsRemoved(1)
                ->save();

            $payload = [
                "success" => true,
                "message" => p__("cabride", "This card is now deleted!"),
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }
}
