<?php

/**
 * Class Payment_Model_Paypal
 *
 * @method string getToken()
 */
class Payment_Model_Paypal extends Payment_Model_Abstract {

    /**
     * Paypal methods definition
     */
    const DO_DIRECT_PAYMENT = 'DoDirectPayment';
    const DO_CAPTURE = 'DoCapture';
    const DO_AUTHORIZATION = 'DoAuthorization';
    const DO_VOID = 'DoVoid';
    const REFUND_TRANSACTION = 'RefundTransaction';
    const SET_EXPRESS_CHECKOUT = 'SetExpressCheckout';
    const GET_EXPRESS_CHECKOUT_DETAILS = 'GetExpressCheckoutDetails';
    const CREATE_RECURRING_PAYMENTS_PROFILE = 'CreateRecurringPaymentsProfile';
    const MANAGE_RECCURING_PAYMENTS_PROFILE = 'ManageRecurringPaymentsProfileStatus';
    const GET_RECURRING_EXPRESS_CHECKOUT_DETAILS = 'GetRecurringPaymentsProfileDetails';
    const DO_EXPRESS_CHECKOUT_PAYMENT = 'DoExpressCheckoutPayment';
    const CALLBACK_RESPONSE = 'CallbackResponse';
    const SALE_ACTION = 'Sale';
    const ORDER_ACTION = 'Order';
    const AUTHORIZATION_ACTION = 'Authorization';

    /**
     * @var mixed|string
     */
    private $__api_url = 'https://api-3t.sandbox.paypal.com/nvp';

    /**
     * @var mixed|string
     */
    private $__paypal_url = 'https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&useraction=commit&token=';

    /**
     * @var string
     */
    private $__pay_url = '';

    /**
     * @var array
     */
    protected $_params = [];

    /**
     * @var array
     */
    protected $_response = [];

    /**
     * @var array
     */
    protected $_errors = [];

    /**
     * @var string
     */
    protected $_return_url = '';

    /**
     * @var string
     */
    protected $_cancel_url = '';

    /**
     * @var null
     */
    protected $_cart = null;

    /**
     * @var Mcommerce_Model_Order|null
     */
    protected $_order = null;

    /**
     * @var null
     */
    protected $_subscription = null;

    /**
     * @var null|string
     */
    public $__user = null;

    /**
     * @var null|string
     */
    public $__pwd = null;

    /**
     * @var null|string
     */
    public $__signature = null;

    /**
     * @var bool
     */
    public $__isTesting = false;

    /**
     * Payment_Model_Paypal constructor.
     * @param bool $user
     * @param bool $pwd
     * @param bool $signature
     * @throws Exception
     */
    public function __construct($user = false, $pwd = false, $signature = false, $isTesting = false) {
        parent::__construct([]);

        $this->_code = 'paypal';

        if ($user && $pwd && $signature) {
            $this->__user = $user;
            $this->__pwd = $pwd;
            $this->__signature = $signature;
            $this->__isTesting = $isTesting;
        } else {
            $providerName = (new Api_Model_Provider())
                ->find('paypal', 'code');
            $keys = $providerName->getKeys();

            foreach ($keys as $key) {
                switch ($key->getKey()) {
                    case 'username':
                        $this->__user = $key->getValue();
                        break;
                    case 'password':
                        $this->__pwd = $key->getValue();
                        break;
                    case 'signature':
                        $this->__signature = $key->getValue();
                        break;
                    case 'is_testing':
                        $this->__isTesting = filter_var($key->getValue(), FILTER_VALIDATE_BOOLEAN);
                        break;
                }
            }

            if (!$this->__user || !$this->__pwd || !$this->__signature) {
                throw new Siberian_Exception('Error, Paypal is not properly set up.');
            }
        }

        // Testing is now a setting and no more dependant on the environment type!
        if (!$this->__isTesting) {
            $this->__api_url = str_replace('sandbox.', '', $this->__api_url);
            $this->__paypal_url = str_replace('sandbox.', '', $this->__paypal_url);
        }
    }

    /**
     * @param $method
     * @param $params
     * @return array|bool
     */
    public function request($method, $params) {
        
        $logger = Zend_Registry::get('logger');

        if (!$this->_isValid()) {
            return false;
        }

        $params = array_merge($params, [
            'METHOD' => $method,
            'VERSION' => '93',
            'USER' => $this->__user,
            'PWD' => $this->__pwd,
            'SIGNATURE' => $this->__signature,
        ]);

        $params = http_build_query($params);

        $curl = curl_init();
        $curlParams = [
            CURLOPT_URL => $this->__api_url,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_VERBOSE => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ];
        curl_setopt_array($curl, $curlParams);

        if ($this->__isTesting) {
            curl_setopt($curl, CURLOPT_SSLVERSION, 6);
        } else {
            curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_1 |
                CURL_SSLVERSION_MAX_DEFAULT);
        }

        $response = curl_exec($curl);
        $responseArray = [];

        parse_str($response, $responseArray);

        if (curl_errno($curl)) {
            $this->_errors = curl_error($curl);
            $this->_params = $params;
            curl_close($curl);
            $logger->log('CURL error n° ' . print_r($this->_errors, true) .
                ' - response: '. print_r($response, true),
                Zend_Log::DEBUG);
        
            return false;
        } else {
            if ($responseArray['ACK'] === 'Success') {
                curl_close($curl);

                if(!empty($responseArray['TOKEN']) && $token = $responseArray['TOKEN']) {
                    $this->__pay_url = $this->__paypal_url . $responseArray['TOKEN'];
                } else {
                    $this->_response = $responseArray;
                }

                return $responseArray;
            } else {
                $this->_errors = $responseArray;
                $this->_params = $params;
                curl_close($curl);
                $logger->log('CURL error: ' . print_r($this->_errors, true), Zend_Log::DEBUG);
                return false;
            }
        }
    }

    /**
     * @return bool|string
     * @override
     */
    public function getUrl() {
        $logger = Zend_Registry::get('logger');
        if (!$this->_isValid()) {
            return false;
        }

        $order = $this->getOrder();
        $params = [
            'RETURNURL' => $this->getReturnUrl(),
            'CANCELURL' => $this->getCancelUrl(),
            'PAYMENTREQUEST_0_CURRENCYCODE' => Core_Model_Language::getCurrentCurrency()->getShortName()
        ];

        foreach ($order->getLines() as $k => $item) {
            if ($item->isRecurrent()) {
                $params['L_PAYMENTREQUEST_0_ITEMCATEGORY0'] = 'Physical';
                $params['L_BILLINGTYPE0'] = 'RecurringPayments';
                $params['L_BILLINGAGREEMENTDESCRIPTION0'] = $item->getName();
            }
        }

        $totalPriceExcludeTax = round($order->getSubtotalExclTax(), 2);
        $totalTax = round($order->getTotalTax(), 2);
        $totalPrice = round($order->getTotal(), 2);

        if ($order->getDeliveryCost() > 0) {
            $deliveryTax = $order->getDeliveryCostInclTax() - $order->getDeliveryCost();
            $totalTax = $totalTax - $deliveryTax;
            $params['PAYMENTREQUEST_0_SHIPPINGAMT'] = round($order->getDeliveryCost() + $deliveryTax, 2);
        }

        // Sum of costs of all items in this order!
        $tmpTotal = round($totalPriceExcludeTax , 2);
        if ($order->getTip()) {
            $tmpTotal += $order->getTip();
        }

        if ($order->getDiscountCode()) {
            $discount = (new Mcommerce_Model_Promo())
                ->find($order->getDiscountCode(), 'code');
            if ($discount->getId()) {
                $cart = $this->getCart();
                $tmpTotal -= $discount->getDeduction($cart);
            }
        }

        // Strange
        $params['L_PAYMENTREQUEST_0_NAME0'] = __('Order: ') . $order->getId();
        $params['L_PAYMENTREQUEST_0_DESC0'] = __('Order: ') . $order->getId();
        $params['L_PAYMENTREQUEST_0_QTY0'] = 1;
        $params['L_PAYMENTREQUEST_0_AMT0'] = round($tmpTotal, 2);

        $params['PAYMENTREQUEST_0_ITEMAMT'] = round($tmpTotal, 2);

        // Sum of tax for all items in this order
        $params['PAYMENTREQUEST_0_TAXAMT'] = round($totalTax, 2);
        
        // Total of order, including shipping, handling, tax, and any other billing adjustments such as a credit due
        $params['PAYMENTREQUEST_0_AMT'] = round($totalPrice, 2);

        $response = $this->request(self::SET_EXPRESS_CHECKOUT, $params);

        if ($response) {
            return $this->__pay_url;
        } else {
            $logger->log('Response error for ' . self::SET_EXPRESS_CHECKOUT .
                ' with params: ' . print_r($params, true), Zend_Log::DEBUG);
            return false;
        }
    }

    /**
     * @return bool
     */
    public function pay() {
        $token = $this->getToken();
        $paymentIsOk = $this->process($token);

        if ($paymentIsOk) {
            $paymentIsOk = $this->createRecurring($token);
        }

        return $paymentIsOk;
    }

    /**
     * @param $token
     * @return bool
     */
    public function process($token) {
        if (!$this->_isValid()) {
            return false;
        }

        if (!$token){
            Zend_Registry::get('logger')->log('Paypal token is missing.', Zend_Log::ERR);
        }
        
        $response = $this->request(self::GET_EXPRESS_CHECKOUT_DETAILS, [
            'TOKEN' => $token
        ]);

        $logger = Zend_Registry::get('logger');
        $logger->debug(print_r($response, true));

        if ($response) {
            if ($response['CHECKOUTSTATUS'] === 'PaymentActionCompleted') {
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

    /**
     * @param $token
     * @return bool
     */
    public function createRecurring($token) {
        $params = [];
        $order = $this->getOrder();
        $lines = $order->getLines();
        $atLeastOneItemIsRecurring = false;

        foreach ($lines as $k => $item) {
            if ($item->isRecurrent()) {
                $k = 0;
                $unitPriceExcludeTax = round($item->getPriceExclTax(), 2);
                $unitPrice = round($unitPriceExcludeTax * (1 + $order->getDbTaxRate() / 100), 2);
                $taxItem = round($unitPrice - $unitPriceExcludeTax, 2);

                $params['DESC'] = $item->getName();
                $params['TAXAMT'] = !empty($params['TAXAMT']) ?
                    $params['TAXAMT'] + $taxItem * $item->getQty() : $taxItem * $item->getQty();
                $params['AMT'] = round($item->getTotalPriceExclTax(), 2);
                $params['L_PAYMENTREQUEST_0_ITEMCATEGORY' . $k] = 'Physical';
                $params['L_PAYMENTREQUEST_0_NAME' . $k] = $item->getName();
                $params['L_PAYMENTREQUEST_0_QTY' . $k] = $item->getQty();
                $params['L_PAYMENTREQUEST_0_TAXAMT' . $k] = $taxItem;
                $params['L_PAYMENTREQUEST_0_AMT' . $k] = round($item->getTotalPriceExclTax(), 2);
                $atLeastOneItemIsRecurring = true;
            }
        }

        if (!empty($params)) {
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

        return !$atLeastOneItemIsRecurring;
    }

    /**
     * @return bool
     */
    public function manageRecurring() {
        if (!$this->getData('is_active')) {
            $paypalRecurringAction = 'SUSPEND';
        } else {
            $paypalRecurringAction = 'REACTIVATE';
        }

        $params = [
            'PROFILEID' => $this->getData('profile_id'),
            'ACTION' => $paypalRecurringAction
        ];

        $response = $this->request(self::MANAGE_RECCURING_PAYMENTS_PROFILE, $params);

        if ($response) {
            return true;
        }

        return false;
    }

    /**
     * @param $order
     * @return array
     * @throws Exception
     */
    public function getPaymentData($order){
        $returnUrl = parent::getUrl('subscription/application/success', [
            'order_id' => $order->getId(),
            'payment_method' => 'paypal'
        ]);
        $cancelUrl = parent::getUrl('subscription/application/cancel', [
            'payment_method' => 'paypal'
        ]);

        $this->setOrder($order)
            ->setReturnUrl($returnUrl)
            ->setCancelUrl($cancelUrl);

        // Redirect to Paypal here
        if(!$paypalUrl = $this->getUrl()) {
            $errors = $this->getErrors();
            $message = __('An error occurred while processing your payment.');
            if (is_array($errors) && !empty($errors['L_LONGMESSAGE0'])) {
                $message .= '<br />' . $errors['L_LONGMESSAGE0'];
            }
            // Really strange log!
            Zend_Registry::get('logger')
                ->sendException('Error when retrieving the Paypal URL:' . PHP_EOL .
                    print_r($this->getErrors(), true). PHP_EOL .
                    'And params:' . print_r($this->getParams(), true),
                    'paypal_error_', false);

            throw new Exception($message);
        } else {
            return [
                'url' => $paypalUrl
            ];
        }
    }

    /**
     * @return array|bool
     */
    public function success () {
        try {
            if ($order = $this->getOrder()) {
                if ($token = $this->getToken()) {
                    if (!$order->getId()) {
                        throw new Siberian_Exception(__('An error occurred while processing your order. Please, try again later.'));
                    }

                    $paymentIsOk = $this->setToken($token)
                        ->setOrder($order)
                        ->pay();

                    if ($paymentIsOk) {
                        $response = $this->getResponse();
                        $data = [
                            'payment_data' => [
                                'profile_id' => !empty($response['PROFILEID']) ?
                                    $response['PROFILEID'] : null,
                                'correlation_id' => !empty($response['CORRELATIONID']) ?
                                    $response['CORRELATIONID'] : null,
                                'is_recurrent' => $order->isRecurrent()
                            ]
                        ];
                        return $data;
                    } else {
                        throw new Siberian_Exception(__('An error occurred while processing the payment. For more information, please feel free to contact us.'));
                    }
                } else {
                    throw new Siberian_Exception(__("An error occurred while processing your order. Please, try again later."));
                }
            } else {
                return false;
            }
        } catch(Exception $e) {
            $this->getSession()->addError($e->getMessage());
            return false;
        }
    }

    /**
     * @return bool
     */
    public function cancel() {
        try {
            if ($orderId = $this->getSession()->order_id) {
                $order = new Sales_Model_Order();
                $order->find($orderId);

                if (!$order->getId()) {
                    throw new Siberian_Exception(__('An error occurred while processing your order. Please, try again later.'));
                }

                $order->cancel();

                $this->getSession()->addWarning(__('Your order has been canceled. If you need any help to place your order, please feel free to contact us.'));
                $this->getSession()->order_id = null;
                $this->getSession()->subscription_id = null;
                
                return true;
            } else {
                return false;
            }
        } catch(Exception $e) {
            $this->getSession()->addError($e->getMessage());
            return false;
        }
    }

    /**
     * @param string|null $period
     * @return string{'Year', 'Month', 'SemiMonth', 'Week', 'Day'}
     */
    public function getPeriod($period = null){
        switch($period) {
            case 'Yearly'  :
                return 'Year' ;
                break;
            case 'Monthly'  :
                return 'Month' ;
                break;
            case 'SemiMonthly'  :
                return 'SemiMonth' ;
                break;
            case 'Weekly'  :
                return 'Week' ;
                break;
            default :
                return 'Day' ;
                break;
        }
    }

    /**
     * @param $period
     * @return string
     */
    public function getNextPaymentDue($period) {

        $date = Zend_Date::now();

        switch($period) {
            case 'Yearly' :
                $date->addYear(1);
                break;
            case 'Monthly' :
                $date->addMonth(1);
                break;
            case 'SemiMonthly' :
                $date->addWeek(2);
                break;
            case 'Weekly' :
                $date->addWeek(1);
                break;
            default :
                $date->addDay(1);
                break;
        }

        return $date->toString("y-MM-dd'T'hh:mm:ss");
    }

    /**
     * @return array
     */
    public function getParams() {
        return $this->_params;
    }

    /**
     * @return array
     */
    public function getResponse() {
        return $this->_response;
    }

    /**
     * @return array
     */
    public function getErrors() {
        return $this->_errors;
    }

    /**
     * @param Mcommerce_Model_Cart $cart
     * @return $this
     */
    public function setCart(Mcommerce_Model_Cart $cart) {
        $this->_cart = $cart;
        return $this;
    }

    /**
     * @return Mcommerce_Model_Cart
     */
    public function getCart() {
        return $this->_cart;
    }

    /**
     * @param Mcommerce_Model_Order $order
     * @return $this
     */
    public function setOrder(/**Mcommerce_Model_Order */$order) {
        $this->_order = $order;
        return $this;
    }

    /**
     * @return Mcommerce_Model_Order
     */
    public function getOrder() {
        return $this->_order;
    }

    /**
     * @param Subscription_Model_Subscription $subscription
     * @return $this
     */
    public function setSubscription(Subscription_Model_Subscription $subscription) {
        $this->_subscription = $subscription;
        return $this;
    }

    /**
     * @return Subscription_Model_Subscription
     */
    public function getSubscription() {
        return $this->_subscription;
    }

    /**
     * @return string
     */
    public function getReturnUrl() {
        return $this->_return_url;
    }

    /**
     * @param $url
     * @return $this
     */
    public function setReturnUrl($url) {
        $this->_return_url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getCancelUrl() {
        return $this->_cancel_url;
    }

    /**
     * @param $url
     * @return $this
     */
    public function setCancelUrl($url) {
        $this->_cancel_url = $url;
        return $this;
    }

    /**
     * @return bool
     */
    protected function _isValid() {
        return !empty($this->__user) && !empty($this->__pwd) && !empty($this->__signature);
    }

    /**
     * @param $code
     * @return bool|stdClass
     */
    public function getSubscriptionInfo($code) {
        $response = $this->request(
            self::GET_RECURRING_EXPRESS_CHECKOUT_DETAILS,
            ['PROFILEID' => $code]);

        if ($response) {
            $return = new stdClass();
            $return->status = strtolower($response['STATUS']);
            return $return;
        } else {
            return false;
        }
    }
}