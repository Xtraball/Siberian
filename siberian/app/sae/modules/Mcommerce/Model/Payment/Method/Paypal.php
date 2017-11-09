<?php

class Mcommerce_Model_Payment_Method_Paypal extends Mcommerce_Model_Payment_Method_Abstract {
    private $_supported_currency_codes = array("AUD","BRL","CAD","CZK","DKK","EUR","HKD","HUF","ILS","JPY","MYR","MXN","NOK","NZD","PHP","PLN","GBP","RUB","SGD","SEK","CHF","TWD","THB","TRY","USD");

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Mcommerce_Model_Db_Table_Payment_Method_Paypal';
        return $this;
    }

    public function pay() {
        $token = $this->getMethod()->getToken();
        $paypal = $this->_getPaypalObject();
        return $paypal->process($token);
    }

    public function getUrl() {
        $cart = $this->getMethod()->getCart();
        $paypal = $this->_getPaypalObject();
        $paypal->setCart($cart);
        $paypal->setOrder($cart);
        return $paypal->getUrl().'&webview=1';
    }

    public function isOnline() {
        return true;
    }

    public function setMethod($method) {

        if($method->getStoreId()) {
            $this->find($method->getStoreId(), 'store_id');
        }

        $this->setData('method', $method);

        return $this;
    }

    public function isCurrencySupported() {
        $currency = Core_Model_Language::getCurrentCurrency();
        return in_array($currency->getShortName(),$this->_supported_currency_codes);
    }

    protected function _getPaypalObject() {
        
        $return_url = parent::getUrl('mcommerce/mobile_sales_confirmation/confirm');
        $cancel_url = parent::getUrl('mcommerce/mobile_sales_confirmation/cancel');
        
        $paypal = new Payment_Model_Paypal($this->getUser(), $this->getPassword(), $this->getSignature());
        $paypal->setReturnUrl($return_url)
            ->setCancelUrl($cancel_url)
            ;

        return $paypal;
    }

}
