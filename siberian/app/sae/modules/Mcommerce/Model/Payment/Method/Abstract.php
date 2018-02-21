<?php

/**
 * Class Mcommerce_Model_Payment_Method_Abstract
 *
 * @method Mcommerce_Model_Payment_Method getMethod()
 */
abstract class Mcommerce_Model_Payment_Method_Abstract extends Core_Model_Default {
    /**
     * @var array
     */
    private $_supported_currency_codes = [];

    /**
     * @param null $id
     * @return bool
     */
    public function pay($id = null) {
        return false;
    }

    /**
     * @return bool
     */
    public function isOnline() {
        return false;
    }

    /**
     * @return bool
     */
    public function isCurrencySupported() {
        return true;
    }

    /**
     * @return mixed
     */
    public function currencySupportApp() {
        return true;
    }

    /**
     * @return string
     */
    public function currencyShortName() {
        return (new Zend_Currency(null, $this->getApplication()->getLocale()))
            ->getShortName();
    }

    /**
     * @return array
     */
    public function getFormUris ($valueId) {
        return [
            'url' => null,
            'form_url' => null
        ];
    }

}
