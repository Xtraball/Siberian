<?php

namespace PaymentStripe\Model;

use Core\Model\Base;
use Stripe\Customer as StripeCustomer;
use Stripe\Error\InvalidRequest;

use Admin_Model_Admin as SiberianAdmin;
use Customer_Model_Customer as SiberianCustomer;
use Application_Model_Application as SiberianApplication;

/**
 * Class Customer
 * @package PaymentStripe\Model
 */
class Customer extends Base
{
    /**
     * @var int
     */
    const TYPE_ADMIN = "admin";

    /**
     * @var int
     */
    const TYPE_CUSTOMER = "customer";

    /**
     * Customer constructor.
     * @param array $params
     * @throws \Zend_Exception
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = 'PaymentStripe\Model\Db\Table\Customer';
        return $this;
    }

    /**
     * @param $customerId
     * @throws \Zend_Exception
     */
    public static function getForAdminId($adminId)
    {
        $customer = (new self())->find($adminId, "admin_id");
        if (!$customer->getId()) {
            $customer = new self();
            $customer
                ->setAdminId($adminId)
                ->save();

            return self::createCustomer($customer, self::TYPE_ADMIN);
        }
        return self::fetchCustomer($customer, self::TYPE_ADMIN);
    }

    /**
     * @param $customerId
     * @throws \Zend_Exception
     */
    public static function getForCustomerId($customerId)
    {
        $customer = (new self())->find($customerId, "customer_id");
        if (!$customer->getId()) {
            $customer = new self();
            $customer
                ->setCustomerId($customerId)
                ->save();

            return self::createCustomer($customer, self::TYPE_CUSTOMER);
        }
        return self::fetchCustomer($customer, self::TYPE_CUSTOMER);
    }

    /**
     * @param $customer
     * @param $type
     * @return \Stripe\ApiResource|StripeCustomer
     * @throws InvalidRequest
     * @throws \Zend_Exception
     */
    public static function fetchCustomer($customer, $type)
    {
        try {
            $stripeCustomer = StripeCustomer::retrieve($customer->getToken());
        } catch (InvalidRequest $e) {
            // Seems the customer doesn't exists anymore, or the Stripe account changed!
            if ($e->getStripeCode() === "resource_missing") {
                // Archiving the old customer reference!
                $customer
                    ->setIsRemoved(1)
                    ->save();

                // Creating a new entry, with the same customerId/adminId!
                $newCustomer = (new self());
                switch ($type) {
                    case self::TYPE_ADMIN:
                        $newCustomer
                            ->setAdminId($customer->getAdminId())
                            ->save();
                        break;
                    case self::TYPE_CUSTOMER:
                        $newCustomer
                            ->setCustomerId($customer->getCustomerId())
                            ->save();
                        break;
                }

                // Creating the new customer in Stripe!
                $stripeCustomer = self::createCustomer($newCustomer, $type);
            } else {
                throw $e;
            }
        }
        return $stripeCustomer;
    }

    /**
     * @param $customer
     * @param $type
     * @return \Stripe\ApiResource
     * @throws \Zend_Exception
     */
    public static function createCustomer($customer, $type)
    {
        switch ($type) {
            case self::TYPE_ADMIN:
                $user = (new SiberianAdmin())->find($customer->getAdminId());
                $userEmail = $user->getEmail();
                $metadata = [
                    "admin_id" => $user->getId(),
                    "app_id" => SiberianApplication::getInstance()->getId(),
                ];
                break;
            case self::TYPE_CUSTOMER:
                $user = (new SiberianCustomer())->find($customer->getCustomerId());
                $userEmail = $user->getEmail();
                $metadata = [
                    "customer_id" => $user->getId(),
                    "app_id" => SiberianApplication::getInstance()->getId(),
                ];
                break;
        }

        $stripeCustomer = StripeCustomer::create([
            "email" => $userEmail,
            "metadata" => $metadata,
        ]);

        // Saving the newly created token!
        $customer
            ->setToken($stripeCustomer["id"])
            ->save();

        return $customer;
    }

}