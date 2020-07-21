<?php

/**
 * Class InAppPurchase_ProductController
 */
class InAppPurchase_ProductController extends Application_Controller_Default
{
    /**
     * These keyworkds can't be used as alias, apple_id or google_id
     *
     * @var array
     */
    public static $reservedKeywords = [
        'product',
        'order',
        'registered',
        'valid',
        'invalid',
        'requested',
        'initiated',
        'approved',
        'owned',
        'finished',
        'downloading',
        'downloaded',
        'refreshed',
    ];

    public function saveAction()
    {
        //try {
        //    $request = $this->getRequest();
        //    $application = $this->getApplication();
        //    $appId = $application->getId();
        //    $form = new FormSettings();
        //    $values = $request->getPost();
        //    if ($form->isValid($values)) {
//
        //        $cashApplication = (new CashApplication())->find($appId, "app_id");
//
        //        $cashApplication
        //            ->setAppId($appId)
        //            ->addData($values)
        //            ->save();
//
        //        $payload = [
        //            "success" => true,
        //            "message" => p__("payment_cash", "Cash settings saved."),
        //        ];
        //    } else { // On form error!
        //        $payload = [
        //            "error" => true,
        //            "message" => $form->getTextErrors(),
        //            "errors" => $form->getTextErrors(true),
        //        ];
        //    }
        //} catch (\Exception $e) {
        //    $payload = [
        //        "error" => true,
        //        "message" => $e->getMessage(),
        //    ];
        //}
//
        //$this->_sendJson($payload);
    }
}
