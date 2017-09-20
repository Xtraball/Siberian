<?php

class Mcommerce_Mobile_Sales_PaymentController extends Mcommerce_Controller_Mobile_Default
{

    public function findonlinepaymenturlAction()
    {

        $method = $this->getCart()->getPaymentMethod();

        $url = null;
        $form_url = null;

        $value_id = $this->getCurrentOptionValue()->getId();

        if ($method->isOnline()) {
            if ($method->getCode() == "stripe") {
                $form_url = $method->getFormUrl($value_id);
            } else {
                $url = $method->getUrl($value_id);
            }
        }

        $html = array(
            "url" => $url,
            "form_url" => $form_url
        );

        $this->_sendHtml($html);
    }

    public function findpaymentmethodsAction()
    {

        $option = $this->getCurrentOptionValue();

        $cart = $this->getCart();

        if(floatval($cart->getTotal()) > 0) {
            $paymentMethods = $cart->getStore()->getPaymentMethods();

            $html = array("paymentMethods" => array());

            foreach ($paymentMethods as $paymentMethod) {

                $paymentMethodJson = array(
                    "id" => $paymentMethod->getId(),
                    "name" => $paymentMethod->getName(),
                    "code" => $paymentMethod->getCode()
                );

                if ($paymentMethod->isOnline()) {
                    if ($paymentMethod->isCurrencySupported()) {
                        $html["paymentMethods"][] = $paymentMethodJson;
                    }
                } else {
                    $html["paymentMethods"][] = $paymentMethodJson;
                }
            }
        }

        if(floatval($cart->getTotal()) <= 0) {
            $free_method = new Mcommerce_Model_Payment_Method();
            $free_method = $free_method->find("free", "code");

            if ($free_method->getId()) {
                $free_data = array(
                    "id" => $free_method->getId(),
                    "name" => $free_method->getName(),
                    "code" => $free_method->getCode()
                );
                $html["paymentMethods"][] = $free_data;
            }
        }


        $this->_sendHtml($html);
    }


    public function updateAction()
    {

        if ($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            $datas = $data["form"];

            $html = array();

            try {

                if (empty($datas['payment_method_id'])) throw new Exception($this->_('Please choose a payment method'));

                $this->getCart()
                    ->setPaymentMethodId($datas['payment_method_id'])
                    ->save();

                $url = $this->getCart()->getPaymentMethod()->getUrl();

                if (!Zend_Uri::check($url)) {
                    $payment_method_name = $this->getCart()->getPaymentMethod()->getName();
                    $this->getCart()
                        ->setPaymentMethodId(null)
                        ->save();

                    $logger = Zend_Registry::get("logger");

                    $logger->log("We apologize but the payment method " . $payment_method_name . " is currently not available at URL: " . $url, Zend_Log::ERR);

                    throw new Siberian_Exception($this->_("We apologize but the payment method %s is currently not available", $payment_method_name));
                }

                $html = array(
                    'payment_method_id' => $this->getCart()->getPaymentMethodId()
                );

            } catch (Exception $e) {
                $html = array(
                    'error' => 1,
                    'message' => $e->getMessage()
                );
            }


            $this->_sendHtml($html);
        }

    }

    public function validatepaymentAction() {
        try {
            if ($data = $this->getRequest()->getRawBody()) {
                $data = Siberian_Json::decode($data);
            } else if ($data = $this->getRequest()->getPost()) {
                $data = $this->getRequest()->getPost();
            } else {
                $data = $this->getRequest()->getFilteredParams();
            }

            if (!empty($data)) {
                try {
                    $errors = $this->getCart()->check();
                    $status_id = Mcommerce_Model_Order::DEFAULT_STATUS;

                    if (empty($errors) AND $this->getCart()->getPaymentMethod()->isOnline()) {
                        $payment_is_valid = $this->getCart()->getPaymentMethod()->addData($data)->pay();
                        if (!$payment_is_valid) {
                            throw new Exception($this->_('An error occurred while proceeding the payment. Please, try again later.'));
                        } else {
                            $status_id = Mcommerce_Model_Order::PAID_STATUS;
                        }
                    }

                    if (empty($errors)) {
                        // Keep a log of the promo and code if used
                        $promo = $this->getPromo();
                        $cart = $this->getCart();
                        $cart->setCustomerUUID($data["customer_uuid"]);

                        if($promo){
                            $log = Mcommerce_Model_Promo_Log::createInstance($promo, $cart);
                            $log->save();

                            //Use points if needed
                            if($promo->getPoints() AND $cart->getCustomerId()) {
                                $points = $promo->getPoints();
                                $customer = new Customer_Model_Customer();
                                $customer->find($cart->getCustomerId());
                                if($customer->getId()) {
                                    $customer_points = $customer->getMetaData("fidelity_points", "points") * 1;
                                    $customer_points -= $points;
                                    $customer->setMetadata("fidelity_points", "points", $customer_points)->save();
                                }
                            }
                        }

                        $order = new Mcommerce_Model_Order();
                        $order->fromCart($this->getCart())->setStatusId($status_id);
                        // TG-459
                        array_key_exists("notes", $data) ? $order->setNotes($data['notes']):$order->setNotes("");
                        $order->save();

                        if (in_array($this->getCart()->getPaymentMethod()->getCode(), array("check", "cc_upon_delivery", "paypal"))) {
                            $order->setHidePaidAmount(true);
                        }
                        $order->sendToCustomer();
                        $order->sendToStore();

                        $message = $this->_('We thank you for your order. A confirmation email has been sent');

                        $html = array(
                            'success' => 1,
                            'message' => $message
                        );

                        $this->getSession()->unsetCart();
                    } else {
                        $message = $this->_('An error occurred while proceeding your order:');
                        foreach ($errors as $error) {
                            $message .= "<br /> - $error";
                        }
                        throw new Exception($message);
                    }

                } catch (Exception $e) {
                    $message = $e->getMessage();
                    $this->getSession()->addError($message);
                    $html = array(
                        'error' => 1,
                        'message' => $message
                    );
                }

                // Mode browser/webapp!
                if ($this->getApplication()->useIonicDesign() && empty($data["is_ajax"])) {
                    if (isset($html["success"])) {
                        $this->_redirect('mcommerce/mobile_sales_success/index', [
                            'value_id' => $this->getCurrentOptionValue()->getValueId()
                        ]);
                    }

                    if (isset($html["error"])) {
                        $this->_redirect('mcommerce/mobile_sales_error/index', [
                            'value_id' => $this->getCurrentOptionValue()->getValueId()
                        ]);
                    }
                }

                if (!empty($data["is_ajax"])) {
                    $this->_sendJson($html, true);
                } elseif (isset($html["error"])) {
                    $this->_redirect('mcommerce/mobile_sales_error/index', [
                        'value_id' => $this->getCurrentOptionValue()->getValueId()
                    ]);
                } elseif (isset($html["success"])) {
                    $this->_sendJson($html, true);
                }

            }

        } catch (Exception $e) {
            $html = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($html);
    }

    public function printToGCPAction()
    {
        $url = "https://accounts.google.com/o/oauth2/auth";

        $params = array(
            "response_type" => "code",
            "client_id" => "AIzaSyAdJLaZN80eGT7Q7RKIxwc3SAsS2U1oMqE.apps.googleusercontent.com",
            "redirect_uri" => "http://localhost/oauth2callback.php",
            "scope" => "https://www.googleapis.com/auth/plus.me"
        );

        $request_to = $url . '?' . http_build_query($params);

        header("Location: " . $request_to);
    }

}
