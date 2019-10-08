<?php

use PaymentMethod\Controller\AbstractMobilePaymentController;

/**
 * Class PaymentStripe_Mobile_HandlerController
 */
class PaymentStripe_Mobile_HandlerController
    extends AbstractMobilePaymentController
{
    public function authorizationSuccessAction()
    {
        $this->__debug();
    }

    public function authorizationErrorAction()
    {
        $this->__debug();
    }

    public function captureSuccessAction()
    {
        $this->__debug();
    }

    public function captureErrorAction()
    {
        $this->__debug();
    }

    public function paymentSuccessAction()
    {
        $this->__debug();
    }

    public function paymentErrorAction()
    {
        $this->__debug();
    }

    private function __debug()
    {
        try {
            $this->_sendJson([
                "success" => true,
                "params" => $this->getRequest()->getBodyParams(),
            ]);
        } catch (\Exception $e) {
            $this->_sendJson([
                "error" => true,
                "message" => $e->getMessage(),
            ]);
        }
    }
}
