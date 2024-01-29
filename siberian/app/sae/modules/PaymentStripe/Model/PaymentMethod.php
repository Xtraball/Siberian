<?php

namespace PaymentStripe\Model;

use Core\Model\Base;
use PaymentStripe\Model\Application as PaymentStripeApplication;
use Siberian\Json;

/**
 * Class PaymentMethod
 * @package PaymentStripe\Model
 *
 * @method Db\Table\PaymentMethod getTable()
 * @method integer getId()
 * @method string getType()
 * @method string getBrand()
 * @method string getExp()
 * @method string getLast()
 * @method boolean getIsLastUsed()
 * @method boolean getIsFavorite()
 * @method $this setStripeCustomerId(integer $customerId)
 * @method $this setType(string $type)
 * @method $this setBrand(string $brand)
 * @method $this setExp(string $expData)
 * @method $this setLast(string $lastDigits)
 * @method $this setIsRemoved(bool $removed)
 */
class PaymentMethod extends Base
{
    /**
     * @var string
     */
    const TYPE_CREDIT_CARD = 'credit-card';

    /**
     * @var string
     */
    protected $_db_table = Db\Table\PaymentMethod::class;

    /**
     * @param $adminId
     * @param array $values
     * @return mixed
     * @throws \Zend_Exception
     */
    public function getForAdminId ($adminId, $values = [])
    {
        return $this->getTable()->getForAdminId($adminId, $values);
    }

    /**
     * @param $customerId
     * @param array $values
     * @return mixed
     * @throws \Zend_Exception
     */
    public function getForCustomerId ($customerId, $values = [])
    {
        return $this->getTable()->getForCustomerId($customerId, $values);
    }

    /**
     * @param string $token
     * @return $this
     * @throws \Siberian\Exception
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
     * @throws \Siberian\Exception
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
     * @param string $token
     * @return $this
     * @throws \Siberian\Exception
     * @throws \Zend_Exception
     */
    public function setRawPayload (string $token): self
    {
        if (PaymentStripeApplication::getMode() === 'live') {
            $this->setData('raw_payload', Json::encode($token));
        } else {
            $this->setData('test_raw_payload', Json::encode($token));
        }

        return $this;
    }

    /**
     * @return string
     * @throws \Siberian\Exception
     * @throws \Zend_Exception
     */
    public function getRawPayload (): string
    {
        if (PaymentStripeApplication::getMode() === 'live') {
            return Json::decode($this->getData('raw_payload'));
        }
        return Json::decode($this->getData('test_raw_payload'));
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
            'type' => (string) $this->getType(),
            'brand' => (string) $this->getBrand(),
            'exp' => (string) $this->getExp(),
            'last' => (string) $this->getLast(),
            'is_last_used' => (boolean) $this->getIsLastUsed(),
            'is_favorite' => (boolean) $this->getIsFavorite(),
        ];

        return $payload;
    }
}