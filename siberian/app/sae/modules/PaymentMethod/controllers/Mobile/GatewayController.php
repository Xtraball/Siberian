<?php

use PaymentMethod\Model\Gateway;

/**
 * Class PaymentMethod_Mobile_GatewayController
 */
class PaymentMethod_Mobile_GatewayController extends Application_Controller_Mobile_Default
{
    public function fetchAllAction()
    {
        try {
            $gateways = Gateway::all();

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