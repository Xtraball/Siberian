<?php

namespace PaymentStripe\Model;

use Core\Model\Base;
use PaymentStripe\Model\Application as PaymentStripeApplication;
use Siberian\Exception;

/**
 * Class PaymentIntent
 * @package PaymentStripe\Model
 *
 * @method Db\Table\PaymentIntent getTable()
 * @method integer getId()
 * @method string getStatus()
 */
class PaymentIntent extends Base
{
    /**
     * @var string
     */
    protected $_db_table = Db\Table\PaymentIntent::class;

    /**
     * @param $reason
     * @param null $cron
     */
    public function cancel($reason, $cron = null)
    {

    }

    /**
     * @return array|mixed|string|null
     * @throws Exception
     * @throws \Zend_Exception
     */
    public function getToken ()
    {
        return PaymentStripeApplication::isLive() ?
            $this->getData('token') : $this->getData('test_token');
    }

    /**
     * @param $token
     * @return PaymentIntent
     * @throws Exception
     * @throws \Zend_Exception
     */
    public function setToken($token): PaymentIntent
    {
        return PaymentStripeApplication::isLive() ?
            $this->setData('token', $token) : $this->setData('test_token', $token);
    }

    /**
     * @throws \Zend_Exception
     */
    public function getPaymentMethod()
    {
        return (new \PaymentStripe\Model\PaymentMethod())->find($this->getPmId());
    }

    /**
     * @return array|string
     * @throws Exception
     * @throws \Zend_Exception
     */
    public function toJson($optionValue = null, $baseUrl = "")
    {
        $payload = [
            'id' => (integer) $this->getId(),
            'token' => (string) $this->getToken(),
            'status' => (string) $this->getStatus(),
        ];

        return $payload;
    }
}