<?php

namespace PaymentStripe\Model;

use Core\Model\Base;
use Stripe\Customer as StripeCustomer;
use Stripe\Error\InvalidRequest;

use Admin_Model_Admin as SiberianAdmin;
use Customer_Model_Customer as SiberianCustomer;
use Application_Model_Application as SiberianApplication;
use Siberian\Exception;

/**
 * Class Customer
 * @package PaymentStripe\Model
 */
class Customer extends Base
{
    /**
     * @var int
     */
    const TYPE_ADMIN = 'admin';

    /**
     * @var int
     */
    const TYPE_CUSTOMER = 'customer';

    /**
     * @var string
     */
    protected $_db_table = Db\Table\Customer::class;

    /**
     * @param $adminId
     * @return mixed|StripeCustomer
     * @throws Exception
     * @throws InvalidRequest
     * @throws \Zend_Exception
     */
    public static function getForAdminId($adminId)
    {
        return self::fetchCustomer($adminId, self::TYPE_ADMIN);
    }

    /**
     * @param $customerId
     * @return mixed|StripeCustomer
     * @throws Exception
     * @throws InvalidRequest
     * @throws \Zend_Exception
     */
    public static function getForCustomerId($customerId)
    {
        return self::fetchCustomer($customerId, self::TYPE_CUSTOMER);
    }

    /**
     * @param $adminOrCustomerId
     * @param $type
     * @return mixed|StripeCustomer
     * @throws Exception
     * @throws InvalidRequest
     * @throws \Zend_Exception
     */
    public static function fetchCustomer($adminOrCustomerId, $type)
    {
        // First, we fetch or create a payment_stripe_customer!
        $paymentStripeCustomer = new self();
        switch ($type) {
            case self::TYPE_ADMIN:
                $paymentStripeCustomer->find([
                    'admin_id' => $adminOrCustomerId,
                    'is_removed' => 0
                ]);
                if (!$paymentStripeCustomer->getId()) {
                    $paymentStripeCustomer
                        ->setAdminId($adminOrCustomerId)
                        ->save();
                }
                break;
            case self::TYPE_CUSTOMER:
                $paymentStripeCustomer->find([
                    'customer_id' => $adminOrCustomerId,
                    'is_removed' => 0
                ]);
                if (!$paymentStripeCustomer->getId()) {
                    $paymentStripeCustomer
                        ->setCustomerId($adminOrCustomerId)
                        ->save();
                }
                break;
        }

        // Ok now we have for sure a payment_stripe_customer, we must find or create a stripe_customer!
        $stripeCustomerToken = trim($paymentStripeCustomer->getToken());
        if (mb_strlen($stripeCustomerToken) <= 0) {
            // Ok we create the Stripe customer then
            switch ($type) {
                case self::TYPE_ADMIN:
                    $siberianAdmin = (new SiberianAdmin())->find($paymentStripeCustomer->getAdminId());
                    $userEmail = $siberianAdmin->getEmail();
                    $metadata = [
                        'admin_id' => $siberianAdmin->getId(),
                        'app_id' => SiberianApplication::getApplication()->getId(),
                    ];
                    break;
                case self::TYPE_CUSTOMER:
                    $siberianCustomer = (new SiberianCustomer())->find($paymentStripeCustomer->getCustomerId());
                    $userEmail = $siberianCustomer->getEmail();
                    $metadata = [
                        'customer_id' => $siberianCustomer->getId(),
                        'app_id' => SiberianApplication::getApplication()->getId(),
                    ];
                    break;
                default:
                    throw new Exception(p__('payment_stripe', 'Invalid user type.'));
            }

            $stripeCustomer = StripeCustomer::create([
                'email' => $userEmail,
                'metadata' => $metadata,
            ]);

            $paymentStripeCustomer
                ->setToken($stripeCustomer['id'])
                ->save();
        } else {
            // We just try to fetch the stripe_customer, if it's ko, we'll get an exception!
            StripeCustomer::retrieve($stripeCustomerToken);
        }

        // Ok!
        return $paymentStripeCustomer;
    }

    /**
     * @return StripeCustomer
     */
    public function getStripeCustomer ()
    {
        return StripeCustomer::retrieve($this->getToken());
    }

}