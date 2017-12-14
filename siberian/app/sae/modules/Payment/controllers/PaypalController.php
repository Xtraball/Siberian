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
        } catch (Exception $e) {
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
        } catch (Exception $e) {
            $this->_redirect('mcommerce/mobile_sales_error/index');
        }
    }

    /**
     * Handles old style cron!
     */
    public function checkrecurrencesAction() {
        if (!Cron_Model_Cron::isRunning()) {
            self::checkRecurrencies();
        } else {
            die('The core CRON Scheduler is handling this request, exiting.');
        }
        die('Done.');
    }

    /**
     * New version as of 4.12.20 to check paypal payment recurrencies!
     */
    public static function checkRecurrencies() {
        $subscriptions = (new Subscription_Model_Subscription_Application())
            ->findExpiredSubscriptions('profile_id');

        $paypalModel = new Payment_Model_Paypal();
        foreach ($subscriptions as $subscription) {
            $response = $paypalModel->request(
                Payment_Model_Paypal::GET_RECURRING_EXPRESS_CHECKOUT_DETAILS,
                [
                    'PROFILEID' => $subscription->getProfileId()
                ]
            );

            $status = strtolower($response['STATUS']);

            if (!in_array($status, ['suspended', 'cancelled'])) {

                $failedPayments = intval($response['FAILEDPAYMENTCOUNT']);
                $paypalDate = new Zend_Date($response['NEXTBILLINGDATE'],
                    'yyyy-MM-dd"T"HH:mm:ss"Z"'
                );

                // Payment OK!
                if ($failedPayments === 0) {
                    $expiresAt = new Zend_Date($subscription->getExpireAt());
                    if ($paypalDate->compare($expiresAt, Zend_Date::DATES) >= 0) {
                        $lastPaymentDate = new Zend_Date(
                            $response['LASTPAYMENTDATE'],
                            'yyyy-MM-dd"T"HH:mm:ss"Z"'
                        );
                        $nextBillingDate = new Zend_Date(
                            $response['NEXTBILLINGDATE'],
                            'yyyy-MM-dd"T"HH:mm:ss"Z"'
                        );
                        $subscription
                            ->setIsActive(1)
                            ->update($nextBillingDate)
                            ->invoice($lastPaymentDate);
                    }
                } else {
                    // Payment error!
                    $subscription
                        ->setIsActive(0)
                        ->save();
                }
            } else {
                // Payment suspended!
                $subscription
                    ->setIsActive(0)
                    ->save();
            }
        }
    }
}