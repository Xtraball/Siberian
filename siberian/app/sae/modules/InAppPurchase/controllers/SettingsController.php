<?php

use InAppPurchase\Form\Settings as FormSettings;
use InAppPurchase\Model\Settings as ModelSettings;

/**
 * Class InAppPurchase_SettingsController
 */
class InAppPurchase_SettingsController extends Application_Controller_Default
{
    public function productsAction()
    {
        $this->loadPartials();
    }

    public function purchasesAction()
    {
        $this->loadPartials();
    }

    public function generalAction()
    {
        $this->loadPartials();
    }


    public function saveAction()
    {
        try {
            $request = $this->getRequest();
            $application = $this->getApplication();
            $appId = $application->getId();
            $form = new FormSettings();
            $values = $request->getPost();
            if ($form->isValid($values)) {

                $settings = new ModelSettings();
                $settings = $settings->find($appId, 'app_id');

                $settings
                    ->setAppId($appId)
                    ->setGoogleBillingKey($values['google_billing_key'])
                    ->save();

                $payload = [
                    'success' => true,
                    'message' => p__('iap', 'Settings saved'),
                ];
            } else { // On form error!
                $payload = [
                    'error' => true,
                    'message' => $form->getTextErrors(),
                    'errors' => $form->getTextErrors(true),
                ];
            }
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }
}
