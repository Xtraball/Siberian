<?php

namespace PaymentStripe\Model;

use Core\Model\Base;

/**
 * Class PaymentMethod
 * @package PaymentStripe\Model
 *
 * @method Db\Table\PaymentMethod getTable()
 * @method integer getId()
 * @method string getToken()
 * @method string getType()
 * @method string getBrand()
 * @method string getExp()
 * @method string getLast()
 * @method boolean getIsLastUsed()
 * @method boolean getIsFavorite()
 */
class PaymentMethod extends Base
{
    /**
     * @var string
     */
    const TYPE_CREDIT_CARD = "credit-card";

    /**
     * PaymentMethod constructor.
     * @param array $params
     * @throws \Zend_Exception
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = 'PaymentStripe\Model\Db\Table\PaymentMethod';
    }

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
     * @return array|string
     */
    public function toJson()
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