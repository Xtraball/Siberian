<?php

class Payment_Model_Paypal extends Core_Model_Default {

    /**
     * Paypal methods definition
     */
    const DO_DIRECT_PAYMENT                         = 'DoDirectPayment';
    const DO_CAPTURE                                = 'DoCapture';
    const DO_AUTHORIZATION                          = 'DoAuthorization';
    const DO_VOID                                   = 'DoVoid';
    const REFUND_TRANSACTION                        = 'RefundTransaction';
    const SET_EXPRESS_CHECKOUT                      = 'SetExpressCheckout';
    const GET_EXPRESS_CHECKOUT_DETAILS              = 'GetExpressCheckoutDetails';
    const CREATE_RECURRING_PAYMENTS_PROFILE         = 'CreateRecurringPaymentsProfile';
    const MANAGE_RECCURING_PAYMENTS_PROFILE         = 'ManageRecurringPaymentsProfileStatus';
    const GET_RECURRING_EXPRESS_CHECKOUT_DETAILS    = 'GetRecurringPaymentsProfileDetails';
    const DO_EXPRESS_CHECKOUT_PAYMENT               = 'DoExpressCheckoutPayment';
    const CALLBACK_RESPONSE                         = 'CallbackResponse';

    /**
     * Paypal actions definition
     */
    const SALE_ACTION           = 'Sale';
    const ORDER_ACTION          = 'Order';
    const AUTHORIZATION_ACTION  = 'Authorization';

    private $__api_url   =   "https://api-3t.sandbox.paypal.com/nvp";
    private $__paypal_url=   "https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&useraction=commit&token=";
    private $__pay_url   =   "";

    protected $_params   =   array();
    protected $_response =   array();
    protected $_errors   =   array();
    protected $_return_url  = "";
    protected $_cancel_url  = "";

    protected $_cart                =   null;
    protected $_order                =   null;
    protected $_subscription        =   null;

    public function __construct($user = false, $pwd = false, $signature = false) {

        $this->_code = "paypal";

        if($user AND $pwd AND $signature) {

            $this->__user = $user;
            $this->__pwd = $pwd;
            $this->__signature = $signature;

        } else {

            $provider_name = new Api_Model_Provider();
            $provider_name->find("paypal", "code");
            $keys = $provider_name->getKeys();

            foreach ($keys as $key) {
                switch ($key->getKey()) {
                    case "username":
                        $this->__user = $key->getValue();
                        break;
                    case "password":
                        $this->__pwd = $key->getValue();
                        break;
                    case "signature":
                        $this->__signature = $key->getValue();
                        break;
                }
            }

            if (!$this->__user OR !$this->__pwd OR !$this->__signature) {
                throw new Exception("Error, Paypal is not properly set up.");
            }
        }

        if($this->isProduction()) {
            $this->__api_url = str_replace('sandbox.', '', $this->__api_url);
            $this->__paypal_url = str_replace('sandbox.', '', $this->__paypal_url);
        }

    }

    public function request($method, $params) {
        
        $logger = Zend_Registry::get("logger");

        if(!$this->_isValid()) {
            return false;
        }

        $params = array_merge($params, array(
            'METHOD'        =>  $method,
            'VERSION'       =>  '74.0',
            'USER'          =>  $this->__user,
            'PWD'           =>  $this->__pwd,
            'SIGNATURE'     =>  $this->__signature,
        ));

        $orig_params = $params;
        $params = http_build_query($params);

        $curl = curl_init();
        $curlParams = array(
            CURLOPT_URL             =>  $this->__api_url,
            CURLOPT_POST            =>  1,
            CURLOPT_POSTFIELDS      =>  $params,
            CURLOPT_RETURNTRANSFER  =>  1,
            CURLOPT_VERBOSE         =>  1,
            CURLOPT_SSL_VERIFYPEER  =>  false, //si certificat SSL => true
            CURLOPT_SSL_VERIFYHOST  =>  false, //si certificat SSL => 2
            //CURLOPT_CAINFO          => ../../.. // si certificat SSL => chemin absolut du fichier PEM
        );
        curl_setopt_array($curl, $curlParams);

        /** @todo testing in production for integration */
        if(APPLICATION_ENV == "development") {
            curl_setopt($curl, CURLOPT_SSLVERSION, 6);
        }
//        curl_setopt($curl, CURLOPT_SSL_CIPHER_LIST, 'SSLv3');
        
        $response = curl_exec($curl);

        $responseArray = array();

        parse_str($response, $responseArray);

        if (curl_errno($curl)) {
            $this->_errors = curl_error($curl);
            $this->_params = $params;
            curl_close($curl);
            $logger->log("CURL error n° " . print_r($this->_errors, true) . ' - response: '. print_r($response, true), Zend_Log::DEBUG);
        
            return false;
        } else {

            if ($responseArray['ACK'] === 'Success') {
                curl_close($curl);

                if(!empty($responseArray['TOKEN']) AND $token = $responseArray['TOKEN']) {
                    $this->__pay_url = $this->__paypal_url . $responseArray['TOKEN'];
                } else {
                    $this->_response = $responseArray;
                }

                return $responseArray;

            } else {
                $this->_errors = $responseArray;
                $this->_params = $params;
                curl_close($curl);
                $logger->log("CURL error: ".print_r($this->_errors, true), Zend_Log::DEBUG);
                return false;
            }
        }
    }

    public function getUrl() {
 
        $logger = Zend_Registry::get("logger");
        
        if(!$this->_isValid()) {
            return false;
        }

        $order = $this->getOrder();

        $params = array(
            'RETURNURL' => $this->getReturnUrl(),
            'CANCELURL' => $this->getCancelUrl(),
            'PAYMENTREQUEST_0_CURRENCYCODE' => Core_Model_Language::getCurrentCurrency()->getShortName()
        );

//        $logger->log("Lines in order: ".sizeof($order->getLines()), Zend_Log::DEBUG);

        foreach($order->getLines() as $k => $item) {

            /**if ($item->getTaxRate() != null){
                // cart
                $unit_price_excl_tax = round($item->getPrice(), 2);
                $unit_tax = round($unit_price_excl_tax * $item->getTaxRate() / 100, 2);
                $unit_price =  round($item->getPriceInclTax() + $unit_tax, 2);
            }else{
                // order
                $unit_price_excl_tax = round($item->getPriceExclTax(), 2);
                $unit_price = round($unit_price_excl_tax * (1 + $order->getTaxRate()/100), 2); // prix TTC
                $unit_tax = round($unit_price - $unit_price_excl_tax, 2);
            }*/
            
//            $logger->log("Line ".$k." : ".$unit_price_excl_tax." - ".$unit_price." - ".$unit_tax, Zend_Log::DEBUG);

            if($item->getIsRecurrent() === "1") {
                $params["L_PAYMENTREQUEST_0_ITEMCATEGORY0"] =   "Physical";
                $params["L_BILLINGTYPE0"]                   =   "RecurringPayments";
                $params["L_BILLINGAGREEMENTDESCRIPTION0"]   =   $item->getName();

            }

            /** @wip #1688 removing line by line, which can cause misvalued. */
            /**$params["L_PAYMENTREQUEST_0_NAME$k"]    = $item->getName();
            $params["L_PAYMENTREQUEST_0_DESC$k"]    = '';
            $params["L_PAYMENTREQUEST_0_QTY$k"]     = $item->getQty();
            $params["L_PAYMENTREQUEST_0_ITEMAMT$k"] = (String) $unit_price_excl_tax;
            $params["L_PAYMENTREQUEST_0_TAXAMT$k"]  = (String) $unit_tax;
            $params["L_PAYMENTREQUEST_0_AMT$k"]     = (String) $unit_price_excl_tax;*/

        }

        $total_price_excl_tax = round($order->getSubtotalExclTax(), 2);
        $total_tax =  round($order->getTotalTax(), 2);
        $total_price = round($order->getTotal(), 2);

        //$params['PAYMENTREQUEST_0_DESC'] = $this->_("Mcommerce");

        if($order->getDeliveryCost() > 0) {
            $delivery_tax = $order->getDeliveryCostInclTax() - $order->getDeliveryCost();
            $total_tax = $total_tax - $delivery_tax;
            $params['PAYMENTREQUEST_0_SHIPPINGAMT'] = round($order->getDeliveryCost() + $delivery_tax, 2);
        }

        // Sum of costs of all items in this order
        $tmp_total = round($total_price_excl_tax , 2);
        if($order->getTip()) {
            $tmp_total += $order->getTip();
        }

        if($order->getDiscountCode()) {
            $discount = new Mcommerce_Model_Promo();
            $discount = $discount->find($order->getDiscountCode(), "code");
            if($discount->getId()) {
                $cart = $this->getCart();
                $tmp_total -= $discount->getDeduction($cart);
            }
        }

        $params["PAYMENTREQUEST_0_ITEMAMT"] = round($tmp_total, 2);

        // Sum of tax for all items in this order
        $params["PAYMENTREQUEST_0_TAXAMT"] = round($total_tax, 2);
        
        // Total of order, including shipping, handling, tax, and any other billing adjustments such as a credit due
        $params["PAYMENTREQUEST_0_AMT"] = round($total_price, 2);

        $response = $this->request(self::SET_EXPRESS_CHECKOUT, $params);

        if ($response) {
//            $logger->log("Pay URL: ".$this->__pay_url, Zend_Log::DEBUG);
            return $this->__pay_url;
        } else {
            $logger->log("Response error for ".self::SET_EXPRESS_CHECKOUT." with params: ".print_r($params, true), Zend_Log::DEBUG);
            return false;
        }

    }

    public function pay() {

        $token = $this->getToken();
        $payment_is_ok = $this->process($token);

        if($payment_is_ok) {
           $payment_is_ok = $this->createRecurring($token, $this->getPeriod($this->getOrder()->getSubscription()->getPaymentFrequency()));
        }

        return $payment_is_ok;

    }


    public function process($token) {

        if(!$this->_isValid()) {
            return false;
        }

        if (!$token){
            Zend_Registry::get("logger")->log("Paypal token is missing.", Zend_Log::ERR);
        }
        
        $response = $this->request(self::GET_EXPRESS_CHECKOUT_DETAILS, array('TOKEN' => $token));
        if($response) {

            if($response['CHECKOUTSTATUS'] === 'PaymentActionCompleted') {
                return true;
            }

            $response = $this->request(self::DO_EXPRESS_CHECKOUT_PAYMENT, $response);
            if ($response) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }

    }

    public function createRecurring($token) {

        $params = array();
        $order = $this->getOrder();
        $lines = $order->getLines();
        $at_least_one_item_is_recurring = false;

        foreach ($lines as $k => $item) {

            if($item->getIsRecurrent() == "1") {
                $k = 0;
                $unit_price_excl_tax = round($item->getPriceExclTax(), 2);
                $unit_price = round($unit_price_excl_tax * (1 + $order->getDbTaxRate()/100), 2); // prix TTC
                $tax_item = round($unit_price - $unit_price_excl_tax, 2);

                $params['DESC'] = $item->getName();
                $params['TAXAMT'] = !empty($params['TAXAMT']) ? $params['TAXAMT'] + $tax_item * $item->getQty() : $tax_item * $item->getQty();
                $params['AMT'] = round($item->getTotalPriceExclTax(), 2);
                $params["L_PAYMENTREQUEST_0_ITEMCATEGORY$k"] = 'Physical';
                $params["L_PAYMENTREQUEST_0_NAME$k"] = $item->getName();
                $params["L_PAYMENTREQUEST_0_QTY$k"] = $item->getQty();
                $params["L_PAYMENTREQUEST_0_TAXAMT$k"] = $tax_item;
                $params["L_PAYMENTREQUEST_0_AMT$k"] = round($item->getTotalPriceExclTax(), 2);
                $at_least_one_item_is_recurring = true;
            }
        }

        if(!empty($params)) {

            $frequency = $this->_order->getSubscription()->getPaymentFrequency();
            $date = $this->getNextPaymentDue($frequency);

            $params = array_merge($params, array(
                'TOKEN' => $token,
                'CURRENCYCODE' => Core_Model_Language::getCurrentCurrency()->getShortName(),
                'PROFILESTARTDATE' => $date,
                'BILLINGPERIOD' => $this->getPeriod($frequency),
                'BILLINGFREQUENCY' => 1,
                'EMAIL' => $this->_order->getAdminEmail()
            ));

            $response = $this->request(self::CREATE_RECURRING_PAYMENTS_PROFILE, $params);

            if ($response) {
                return true;
            }
        }

        return !$at_least_one_item_is_recurring;

    }

    public function getPeriod($period = null){
        switch($period) {
            case "Yearly" :
                return "Year";
                break;
            case "Monthly" :
                return "Month";
                break;
            case "SemiMonthly" :
                return "SemiMonth";
                break;
            case "Weekly" :
                return "Week";
                break;
            default :
                return "Day";
                break;
        }
    }

    public function getNextPaymentDue($period) {

        $date = Zend_Date::now();

        switch($period) {
            case "Yearly" :
                $date->addYear(1);
                break;
            case "Monthly" :
                $date->addMonth(1);
                break;
            case "SemiMonthly" :
                $date->addWeek(2);
                break;
            case "Weekly" :
                $date->addWeek(1);
                break;
            default :
                $date->addDay(1);
                break;
        }

        return $date->toString("y-MM-dd'T'hh:mm:ss");
    }

    public function getParams() {
        return $this->_params;
    }

    public function getResponse() {
        return $this->_response;
    }

    public function getErrors() {
        return $this->_errors;
    }

    public function setCart($cart) {
        $this->_cart = $cart;
        return $this;
    }

    public function getCart() {
        return $this->_cart;
    }

    public function setOrder($order) {
        $this->_order = $order;
        return $this;
    }

    public function getOrder() {
        return $this->_order;
    }

    public function setSubscription($subscription) {
        $this->_subscription = $subscription;
        return $this;
    }

    public function getSubscription() {
        return $this->_subscription;
    }

    public function getReturnUrl() {
        return $this->_return_url;
    }

    public function setReturnUrl($url) {
        $this->_return_url = $url;
        return $this;
    }

    public function getCancelUrl() {
        return $this->_cancel_url;
    }

    public function setCancelUrl($url) {
        $this->_cancel_url = $url;
        return $this;
    }

    protected function _isValid() {
        return !empty($this->__user) && !empty($this->__pwd) && !empty($this->__signature);
    }

}
