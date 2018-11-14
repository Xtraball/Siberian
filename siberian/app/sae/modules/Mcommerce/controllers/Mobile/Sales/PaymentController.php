<?php

/**
 * Class Mcommerce_Mobile_Sales_PaymentController
 */
class Mcommerce_Mobile_Sales_PaymentController extends Mcommerce_Controller_Mobile_Default
{

    /**
     *  Fetch payment url for the select one.
     */
    public function findonlinepaymenturlAction()
    {
        try {
            $method = $this->getCart()->getPaymentMethod();
            $valueId = $this->getCurrentOptionValue()->getId();

            $payload = $method->getInstance()->getFormUris($valueId);
        } catch (Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function findpaymentmethodsAction()
    {
        try {
            $cart = $this->getCart();
            $cartTotal = floatval($cart->getTotal());
            if ($cartTotal > 0) {

                $paymentMethods = $cart->getStore()->getPaymentMethods();

                $payload = [
                    'paymentMethods' => []
                ];

                foreach ($paymentMethods as $paymentMethod) {
                    $paymentMethodJson = [
                        'id' => $paymentMethod->getId(),
                        'name' => $paymentMethod->getName(),
                        'code' => $paymentMethod->getCode()
                    ];

                    if ($paymentMethod->isOnline()) {
                        if ($paymentMethod->isCurrencySupported()) {
                            $payload['paymentMethods'][] = $paymentMethodJson;
                        }
                    } else {
                        $payload['paymentMethods'][] = $paymentMethodJson;
                    }
                }
            } else {
                $freeMethod = (new Mcommerce_Model_Payment_Method())
                    ->find('free', 'code');

                if ($freeMethod->getId()) {
                    $freeData = [
                        'id' => $freeMethod->getId(),
                        'name' => $freeMethod->getName(),
                        'code' => $freeMethod->getCode()
                    ];
                    $payload['paymentMethods'][] = $freeData;
                } else {
                    throw new Siberian_Exception('#635-01' . __('Unkown error!'));
                }
            }
        } catch(Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
                
        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function updateAction() {
        try {
            $request = $this->getRequest();
            if ($params = Siberian_Json::decode($request->getRawBody())) {
                $formValues = $params['form'];

                if (empty($formValues['payment_method_id'])) {
                    throw new Siberian_Exception(__('Please choose a payment method'));
                }

                $cart = $this->getCart();
                $cart
                    ->setPaymentMethodId($formValues['payment_method_id'])
                    ->save();

                $url = $cart->getPaymentMethod()->getUrl();
                if (!Zend_Uri::check($url)) {
                    $paymentMethodName = $cart->getPaymentMethod()->getName();
                    $cart
                        ->setPaymentMethodId(null)
                        ->save();

                    $logger = Zend_Registry::get('logger');
                    $logger->log('We apologize but the payment method ' .
                        $paymentMethodName . ' is currently not available at URL: ' . $url,
                        Zend_Log::ERR);

                    throw new Siberian_Exception(
                        __('We apologize but the payment method %s is currently not available',
                            $paymentMethodName));
                }

                $payload = [
                    'success' => true,
                    'payment_method_id' => $cart->getPaymentMethodId()
                ];
            } else {
                throw new Siberian_Exception(__('Missing parameters!'));
            }

        } catch(Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function validatepaymentAction() {
        try {
            $request = $this->getRequest();
            if ($params = $request->getRawBody()) {
                $params = Siberian_Json::decode($params);
            } else if ($data = $request->getPost()) {
                $params = $request->getPost();
            } else {
                $params = $request->getFilteredParams();
            }

            if (empty($params)) {
                throw new Siberian_Exception(__('Missing params!'));
            }

            try {
                $cart = $this->getCart();
                $errors = $cart->check();
                $paymentMethod = $cart->getPaymentMethod();
                $statusId = Mcommerce_Model_Order::DEFAULT_STATUS;

                if (empty($errors) && $paymentMethod->isOnline()) {
                    $paymentIsValid = $paymentMethod
                        ->addData($params)
                        ->setParams($params)
                        ->pay();

                    if (!$paymentIsValid) {
                        throw new Siberian_Exception(
                            __('An error occurred while proceeding the payment. Please, try again later.'));
                    } else {
                        $statusId = Mcommerce_Model_Order::PAID_STATUS;
                    }
                }

                if (empty($errors)) {
                    // Keep a log of the promo and code if used!
                    $promo = $this->getPromo();
                    $cart = $this->getCart();
                    $cart->setCustomerUUID($params['customer_uuid']);

                    if ($promo) {
                        $log = Mcommerce_Model_Promo_Log::createInstance($promo, $cart);
                        $log->save();

                        // Use points if needed!
                        if ($promo->getPoints() && $cart->getCustomerId()) {
                            $points = $promo->getPoints();
                            $customer = (new Customer_Model_Customer())
                                ->find($cart->getCustomerId());
                            // Decrease points!
                            if ($customer->getId()) {
                                $customerPoints = $customer->getMetaData('fidelity_points', 'points') * 1;
                                $customerPoints = $customerPoints - $points;
                                $customer->setMetadata('fidelity_points', 'points', $customerPoints)->save();
                            }
                        }
                    }

                    $order = new Mcommerce_Model_Order();
                    $order
                        ->fromCart($cart)
                        ->setStatusId($statusId);

                    array_key_exists('notes', $params) ?
                        $order->setNotes(\Siberian\Xss::sanitize($params['notes'])) : $order->setNotes('');
                    $order->save();

                    if (in_array($this->getCart()->getPaymentMethod()->getCode(),
                        ['check', 'cc_upon_delivery', 'paypal'])) {
                        $order->setHidePaidAmount(true);
                    }
                    $order->sendToCustomer();
                    $order->sendToStore();

                    $message = __('We thank you for your order. A confirmation email has been sent');

                    $payload = [
                        'success' => true,
                        'message' => $message
                    ];

                    $this->getSession()->unsetCart();
                } else {
                    $message = __('An error occurred while proceeding your order:');
                    foreach ($errors as $error) {
                        $message .= '<br /> - ' . $error;
                    }
                    throw new Siberian_Exception($message);
                }
            } catch (Exception $e) {
                $message = $e->getMessage();
                $this->getSession()->addError($message);
                $payload = [
                    'error' => true,
                    'message' => $message
                ];
            }

            // Mode browser/webapp!
            if ($this->getApplication()->useIonicDesign() && empty($params['is_ajax'])) {
                if (isset($payload['success'])) {
                    $this->getResponse()->setHeader('x-success', $payload['message']);
                    $this->_redirect('mcommerce/mobile_sales_success/index', [
                        'value_id' => $this->getCurrentOptionValue()->getValueId()
                    ]);
                }

                if (isset($payload['error'])) {
                    $this->getResponse()->setHeader('x-error', $payload['message']);
                    $this->_redirect('mcommerce/mobile_sales_error/index', [
                        'value_id' => $this->getCurrentOptionValue()->getValueId()
                    ]);
                }
            }

            if (!empty($params['is_ajax'])) {
                $this->_sendJson($payload, true);
            } elseif (isset($payload['error'])) {
                $this->_redirect('mcommerce/mobile_sales_error/index', [
                    'value_id' => $this->getCurrentOptionValue()->getValueId()
                ]);
            } elseif (isset($payload['success'])) {
                $this->_sendJson($payload, true);
            }
        } catch(Exception $e) {
            $payload = [
                'error' => true,
                'message' => __('An unknown error occurred, please try again later.') . $e->getMessage()
            ];
            $this->_sendJson($payload);
        }
    }
}
