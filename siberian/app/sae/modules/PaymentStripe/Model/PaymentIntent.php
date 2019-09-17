<?php

namespace PaymentStripe\Model;

use Core\Model\Base;

/**
 * Class PaymentIntent
 * @package PaymentStripe\Model
 *
 * @method Db\Table\PaymentIntent getTable()
 */
class PaymentIntent extends Base
{
    /**
     * PaymentIntent constructor.
     * @param array $params
     * @throws \Zend_Exception
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = 'PaymentStripe\Model\Db\Table\PaymentIntent';
        return $this;
    }

    /**
     * @return array|string
     */
    public function toJson()
    {
        $payload = [
            "id" => (integer) $this->getId(),
            "token" => (string) $this->getToken(),
            "status" => (string) $this->getStatus(),
        ];

        return $payload;
    }
}