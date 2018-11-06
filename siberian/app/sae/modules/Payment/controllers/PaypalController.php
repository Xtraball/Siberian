<?php

/**
 * Class Payment_PaypalController
 */
class Payment_PaypalController extends Application_Controller_Mobile_Default
{
    /**
     * Cancel url public action
     */
    public function cancelAction()
    {
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
    public function confirmAction()
    {
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
    public function checkrecurrencesAction()
    {
        if (!Cron_Model_Cron::isRunning()) {
            self::checkRecurrencies();
        } else {
            die('The core CRON Scheduler is handling this request, exiting.');
        }
        die('Done.');
    }

    /**
     * New version as of 4.12.20 to check paypal payment recurrencies!
     *
     * @deprecated
     */
    public static function checkRecurrencies($cronInstance = null)
    {
        $subscriptions = (new Subscription_Model_Subscription_Application())
            ->findExpiredSubscriptions('profile_id');

        try {
            $paypalModel = new Payment_Model_Paypal();
        } catch (Exception $e) {
            if (Siberian_Version::is('PE')) {
                $subscriptionsCount = (new Subscription_Model_Subscription_Application())
                    ->countAll(
                        [
                            'payment_method' => 'paypal'
                        ]
                    );

                if ($subscriptionsCount && $e->getCode() === 100) {
                    throw new Siberian_Exception(
                        __('PayPal is not configured in your Backoffice, recurrencies will not work correctly.'));
                } else {
                    $cronInstance->log('PayPal is not configured in your Backoffice, aborting.');
                }
            }
            return;
        }

        $saleModel = new Sales_Model_Invoice();
        $year2018 = new Zend_Date('2018-01-01 00:00:00Z');
        $countSubscription = count($subscriptions);
        $i = 1;
        if ($cronInstance) {
            $cronInstance->log(count($subscriptions) . " subscriptions to check");
        }
        foreach ($subscriptions as $subscription) {
            if ($cronInstance) {
                $cronInstance->log($i++ . "/" . $countSubscription .
                    " : Checking subscription with profile id " . $subscription->getProfileId()
                );
            }
            $response = $paypalModel->request(
                Payment_Model_Paypal::GET_RECURRING_PAYMENTS_PROFILE_DETAILS,
                [
                    'PROFILEID' => $subscription->getProfileId()
                ]
            );
            if ($cronInstance) {
                $cronInstance->log('(' . $subscription->getProfileId() . ') ' .
                    "status:" .
                    (array_key_exists('STATUS', $response) ? $response['STATUS'] : 'unknow')
                );
            }
            $status = $response['STATUS'];

            // if we cannot get subscription information we postpone operation
            if (!$status) {
                continue;
            }

            // OUTSTANDINGBALANCE is missing payment amount
            if ($status === "Active" && intval($response['OUTSTANDINGBALANCE']) === 0) {

                if ($cronInstance) {
                    $cronInstance->log('(' . $subscription->getProfileId() . ') ' . "Subscription is active");
                }

                $profileStartDate = new Zend_Date($response['PROFILESTARTDATE']);
                // to fix Zend_Date day shifting we set hour as 12:00pm
                $profileStartDate->setHour('12');
                $profileStartDate->setMinute('00');

                $checkingInvoiceDate = clone $profileStartDate;
                $frequency = $subscription->getSubscription()->getPaymentFrequency();

                while ($checkingInvoiceDate->isEarlier(Zend_Date::now())) {
                    switch ($frequency) {
                        case 'Monthly':
                            if (!$saleModel->isInvoiceExistsForMonth(
                                $subscription->getAppId(), $checkingInvoiceDate
                            )) {
                                // @date 23th Mars 2018
                                // we created invoices only since 2018-01-01
                                // indeed some siberian already fix there accounting before
                                // and we don't want to dupplicated fixed invoices
                                if (!$checkingInvoiceDate->isEarlier($year2018)) {
                                    if ($cronInstance) {
                                        $cronInstance->log('(' . $subscription->getProfileId() . ') ' . "Creating invoice (sub monthly) for date " . $checkingInvoiceDate);
                                    }
                                    $subscription->invoice($checkingInvoiceDate, $subscription->getProfileId());
                                }
                            }
                            $checkingInvoiceDate->addMonth(1);
                            break;
                        case 'Yearly':
                            if (!$saleModel->isInvoiceExistsForYear(
                                $subscription->getAppId(), $checkingInvoiceDate
                            )) {
                                // @date 23th Mars 2018
                                // we created invoices only since 2018-01-01
                                // indeed some siberian already fix there accounting before
                                // and we don't want to dupplicated fixed invoices
                                if (!$checkingInvoiceDate->isEarlier($year2018)) {
                                    if ($cronInstance) {
                                        $cronInstance->log('(' . $subscription->getProfileId() . ') ' . "Creating invoice (sub yearly) for date " . $checkingInvoiceDate);
                                    }
                                    $subscription->invoice($checkingInvoiceDate, $subscription->getProfileId());
                                }
                            }
                            $checkingInvoiceDate->addYear(1);
                            break;
                        default:
                            throw new Exception('Error: unknow subscription payment frequency for subscription:' . $subscription->getId());
                    }
                }
                // Payment (re-)activated!
                $nextBillingDate = new Zend_Date($response['NEXTBILLINGDATE']);
                $nextBillingDate->setHour('12');
                $nextBillingDate->setMinute('00');

                $subscription->unlock();
                $subscription
                    ->update($nextBillingDate)
                    ->save();

                // clean the mess
                unset($checkingInvoiceDate);
                unset($profileStartDate);
                unset($nextBillingDate);
                unset($frequency);
            } else {
                // Payment suspended!
                if ($cronInstance) {
                    $cronInstance->log('(' . $subscription->getProfileId() . ') ' . "Subscription is inactive");
                }
                $subscription->lock();
            }
        }

        // clean the mess
        unset($i);
        unset($subscriptions);
        unset($paypalModel);
        unset($saleModel);
        unset($year2018);
    }
}