<?php

use PaymentCash\Form\Settings as FormSettings;
use PaymentCash\Model\Application as CashApplication;

/**
 * Class PaymentCash_SettingsController
 */
class PaymentCash_SettingsController extends Application_Controller_Default
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

                $cashApplication = (new CashApplication())->find($appId, "app_id");

                $cashApplication
                    ->setAppId($appId)
                    ->addData($values)
                    ->save();

                $payload = [
                    "success" => true,
                    "message" => p__("payment_cash", "Cash settings saved."),
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
}