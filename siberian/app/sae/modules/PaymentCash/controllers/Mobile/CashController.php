<?php

use Customer_Model_Customer as Customer;
use PaymentCash\Model\Payment;
use PaymentCash\Model\Cash;
use Siberian\Exception;
use PaymentMethod\Model\Payment as PaymentMethodPayment;

/**
 * Class PaymentCash_Mobile_CashController
 */
class PaymentCash_Mobile_CashController extends Application_Controller_Mobile_Default
{
    /**
     * @throws Zend_Exception
     */
    public function fetchPaymentAction()
    {
        try {
            $application = $this->getApplication();
            $appId = $application->getId();
            $option = $this->getCurrentOptionValue();
            $valueId = $option->getId();
            $request = $this->getRequest();
            $data = $request->getBodyParams();
            $customerId = $this->getSession()->getCustomerId();
            $customer = (new Customer())->find($customerId);

            $options = $data['options'];
            $amount = $options['payment']['amount'];
            $currency = $options['payment']['currency'] ?? $application->getCurrency();

            if (!$customer->getId()) {
                throw new Exception(p__("payment_cash",
                    "Your session expired!"));
            }

            $cashPayment = new Payment();
            $cashPayment
                ->setAppId($appId)
                ->setValueId($valueId)
                ->setCurrency($currency)
                ->setAmount($amount)
                ->setCustomerId($customerId)
                ->setStatus('pending')
                ->save();

            // Attaching to a generic payment
            $payment = PaymentMethodPayment::createOrGetFromModal([
                'id' => $cashPayment->getId(),
                'code' => Cash::$shortName
            ]);

            $payload = [
                'success' => true,
                'paymentId' => (integer) $payment->getId()
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }
}
