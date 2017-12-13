<?php

/**
 * Class Payment_PaypalController
 */
class Payment_PaypalController extends Application_Controller_Mobile_Default {

    /**
     * Cancel url public action
     */
    public function cancelAction () {
        try {
            $request = $this->getRequest();
            $params = $request->getParams();
            usleep(20);
            $this->_redirect('mcommerce/mobile_sales_cancel/index', [
                'cart_id' => $params['cart_id']
            ]);
        } catch(Exception $e) {
            $this->_redirect('mcommerce/mobile_sales_error/index');
        }
    }

    /**
     * Confirm url public action
     */
    public function confirmAction () {
        try {
            $request = $this->getRequest();
            $params = $request->getParams();
            usleep(20);

            $this->_redirect('mcommerce/mobile_sales_payment/validatepayment', [
                'sb-token' => $params['sb-token'],
                'cart_id' => $params['cart_id'],
                'token' => $params['token'],
                'payer_id' => $params['PayerID'],
                'PayerID' => $params['PayerID'],
            ]);
        } catch(Exception $e) {
            $this->_redirect('mcommerce/mobile_sales_error/index');
        }
    }
}