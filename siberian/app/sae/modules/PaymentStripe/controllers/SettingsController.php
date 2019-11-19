<?php

use Siberian\Exception;

use PaymentStripe\Form\Settings as FormSettings;
use PaymentStripe\Model\Application as StripeApplication;

use Stripe\Stripe;
use Stripe\Customer;

/**
 * Class PaymentStripe_SettingsController
 */
class PaymentStripe_SettingsController extends Application_Controller_Default
{
    public function indexAction()
    {
        $this->loadPartials();
    }

    /**
     *
     */
    public function saveAction()
    {
        try {
            $request = $this->getRequest();
            $application = $this->getApplication();
            $appId = $application->getId();
            $form = new FormSettings();
            $values = $request->getPost();
            if ($form->isValid($values)) {
                self::testStripe($values);

                /** Do whatever you need when form is valid */
                $stripeApplication = (new StripeApplication())->find($appId, "app_id");

                // Automatically determines if it's in test mode!
                $values["is_sandbox"] = 0;
                if (preg_match("/_test_/i", $values["publishable_key"]) === 1) {
                    $values["is_sandbox"] = 1;
                }

                $stripeApplication
                    ->setAppId($appId)
                    ->addData($values)
                    ->save();

                $payload = [
                    "success" => true,
                    "message" => p__("payment_stripe", "Stripe API keys saved."),
                ];
            } else { // On form error!
                $payload = [
                    "error" => true,
                    "message" => $form->getTextErrors(),
                    "errors" => $form->getTextErrors(true),
                ];
            }
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * @param $values
     * @throws \Siberian\Exception
     */
    private static function testStripe($values)
    {
        try {
            Stripe::setApiKey($values["secret_key"]);
            Customer::all();
        } catch (\Exception $e) {
            throw new Exception(__("Stripe API Error: %s", $e->getMessage()));
        }
    }
}