<?php

namespace PaymentStripe\Model;

use Core\Model\Base;

/**
 * Class PaymentIntent
 * @package PaymentStripe\Model
 *
 * @method Db\Table\PaymentIntent getTable()
 * @method integer getId()
 * @method string getToken()
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
     * @return array|string
     */
    public function toJson()
    {
        $payload = [
            'id' => (integer) $this->getId(),
            'token' => (string) $this->getToken(),
            'status' => (string) $this->getStatus(),
        ];

        return $payload;
    }
}