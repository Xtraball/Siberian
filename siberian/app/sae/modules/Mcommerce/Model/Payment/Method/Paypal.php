<?php

class Mcommerce_Model_Payment_Method_Paypal extends Mcommerce_Model_Payment_Method_Abstract {
    /**
     * @var array
     */
    private $_supported_currency_codes = [
        'AUD',
        'BRL',
        'CAD',
        'CZK',
        'DKK',
        'EUR',
        'HKD',
        'HUF',
        'ILS',
        'INR',
        'JPY',
        'MYR',
        'MXN',
        'NOK',
        'NZD',
        'PHP',
        'PLN',
        'GBP',
        'RUB',
        'SGD',
        'SEK',
        'CHF',
        'TWD',
        'THB',
        'TRY',
        'USD'
    ];

    /**
     * Mcommerce_Model_Payment_Method_Paypal constructor.
     * @param array $params
     */
    public function __construct($params = []) {
        parent::__construct($params);
        $this->_db_table = 'Mcommerce_Model_Db_Table_Payment_Method_Paypal';
        return $this;
    }

    /**
     * @return bool
     */
    public function pay($id = null) {
        $token = $this->getMethod()->getToken();
        $paypal = $this->_getPaypalObject();
        return $paypal->process($token);
    }

    /**
     * @return string
     *
     * @todo rename to avoid inhreitance conflicts
     */
    public function getUrl() {
        $cart = $this->getMethod()->getCart();
        $paypal = $this->_getPaypalObject();
        $paypal->setCart($cart);
        $paypal->setOrder($cart);

        return $paypal->getUrl() . '&webview=1';
    }

    /**
     * @param $valueId
     * @return array
     */
    public function getFormUris ($valueId) {
        return [
            'url' => $this->getUrl($valueId),
            'form_url' => null
        ];
    }

    /**
     * @return bool
     */
    public function isOnline() {
        return true;
    }

    /**
     * @param $method
     * @return $this
     */
    public function setMethod($method) {
        if ($method->getStoreId()) {
            $this->find($method->getStoreId(), 'store_id');
        }

        $this->setData('method', $method);

        return $this;
    }

    /**
     * @return bool
     */
    public function isCurrencySupported() {
        $currency = Core_Model_Language::getCurrentCurrency();
        return in_array($currency->getShortName(),$this->_supported_currency_codes);
    }

    /**
     * @return mixed
     */
    public function currencySupportApp() {
        return in_array($this->currencyShortName(), $this->_supported_currency_codes);
    }

    /**
     * @return Payment_Model_Paypal
     */
    protected function _getPaypalObject() {
        $cart = $this->getSession()->getCart();

        $returnUrl = parent::getUrl('payment/paypal/confirm', [
            'cart_id' => $cart->getId(),
            'sb-token' => Zend_Session::getId()
        ]);
        $cancelUrl = parent::getUrl('payment/paypal/cancel', [
            'cart_id' => $cart->getId(),
            'sb-token' => Zend_Session::getId()
        ]);
        
        $paypal = new Payment_Model_Paypal(
            $this->getUser(),
            $this->getPassword(),
            $this->getSignature(),
            $this->getIsTesting());

        $paypal
            ->setReturnUrl($returnUrl)
            ->setCancelUrl($cancelUrl);

        return $paypal;
    }
}
