<?php

namespace PaymentMethod\Model;

/**
 * Interface GatewayInterface
 * @package PaymentMethod\Model
 */
interface GatewayInterface
{
    public function authorizationSuccess();

    public function authorizationError();

    public function captureSuccess();

    public function captureError();

    public function paymentSuccess();

    public function paymentError();
}