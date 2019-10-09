<?php

namespace PaymentMethod\Controller;

/**
 * Interface AbstractPaymentInterface
 * @package PaymentMethod\Controller
 */
interface AbstractPaymentInterface
{
    public function authorizationSuccessAction();

    public function authorizationErrorAction();

    public function captureSuccessAction();

    public function captureErrorAction();

    public function paymentSuccessAction();

    public function paymentErrorAction();

    public function setupSuccessAction();

    public function setupErrorAction();
}