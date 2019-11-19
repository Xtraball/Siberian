<?php

use PaymentMethod\Model\Gateway;
use PaymentMethod\Model\GatewayAbstract;

/**
 * Class PaymentMethod_Mobile_GatewayController
 */
class PaymentMethod_Mobile_GatewayController extends Application_Controller_Mobile_Default
{
    /**
     * fetch all active payment methods for the current App
     */
    public function fetchAllAction()
    {
        try {
            $gateways = [];

            $allGateways = Gateway::all();
            foreach ($allGateways as $gateway) {
                /**
                 * @var $_gateway GatewayAbstract
                 */
                $_gateway = new $gateway["class"]();

                // Returns only "setup" gateways!
                if ($_gateway->isSetup()) {
                    $gateways[] = $gateway;
                }
            }

            $payload = [
                "success" => true,
                "gateways" => $gateways
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