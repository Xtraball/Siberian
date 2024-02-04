<?php

namespace PaymentStripe\Model;

use Core\Model\Base;
use Stripe\Customer as StripeCustomer;
use PaymentStripe\Model\Application as PaymentStripeApplication;

use Admin_Model_Admin as SiberianAdmin;
use Customer_Model_Customer as SiberianCustomer;
use Application_Model_Application as SiberianApplication;
use Siberian\Exception;

/**
 * Class Customer
 * @package PaymentStripe\Model
 *
 * @method int getId()
 * @method setAdminId(int $adminId)
 * @method setCustomerId(int $customerId)
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
     * @param string $token
     * @return $this
     * @throws Exception
     * @throws \Zend_Exception
     */
    public function setToken (string $token): self
    {
        if (PaymentStripeApplication::isLive()) {
            $this->setData('token', trim($token));
        } else {
            $this->setData('test_token', trim($token));
        }

        return $this;
    }

    /**
     * @return string
     * @throws Exception
     * @throws \Zend_Exception
     */
    public function getToken (): string
    {
        if (PaymentStripeApplication::isLive()) {
            return trim($this->getData('token'));
        }
        return trim($this->getData('test_token'));
    }

    /**
     * @param $adminId
     * @return Customer
     * @throws Exception
     * @throws \Stripe\Exception\ApiErrorException
     * @throws \Zend_Exception
     */
    public static function getForAdminId($adminId): Customer
    {
        return self::fetchCustomer($adminId, self::TYPE_ADMIN);
    }

    /**
     * @param $customerId
     * @return Customer
     * @throws Exception
     * @throws \Stripe\Exception\ApiErrorException
     * @throws \Zend_Exception
     */
    public static function getForCustomerId($customerId): Customer
    {
        return self::fetchCustomer($customerId, self::TYPE_CUSTOMER);
    }

    /**
     * @param $adminOrCustomerId
     * @param $type
     * @return Customer
     * @throws Exception
     * @throws \Stripe\Exception\ApiErrorException
     * @throws \Zend_Exception
     */
    public static function fetchCustomer($adminOrCustomerId, $type): Customer
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
        $stripeCustomerToken = $paymentStripeCustomer->getToken();
        if (mb_strlen($stripeCustomerToken) <= 0) {
            // Ok we create the Stripe customer then
            switch ($type) {
                case self::TYPE_ADMIN:
                    $siberianAdmin = (new SiberianAdmin())->find($paymentStripeCustomer->getAdminId());
                    $userEmail = $siberianAdmin->getEmail();
                    $metadata = [
                        'admin_id' => $siberianAdmin->getId(),
                        'app_id' => SiberianApplication::sGetApplication()->getId(),
                    ];
                    break;
                case self::TYPE_CUSTOMER:
                    $siberianCustomer = (new SiberianCustomer())->find($paymentStripeCustomer->getCustomerId());
                    $userEmail = $siberianCustomer->getEmail();
                    $metadata = [
                        'customer_id' => $siberianCustomer->getId(),
                        'app_id' => SiberianApplication::sGetApplication()->getId(),
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
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function getStripeCustomer (): StripeCustomer
    {
        return StripeCustomer::retrieve($this->getToken());
    }

}