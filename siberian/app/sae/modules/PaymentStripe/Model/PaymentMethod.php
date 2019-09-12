<?php

namespace PaymentStripe\Model;

use Core\Model\Base;

/**
 * Class PaymentMethod
 * @package PaymentStripe\Model
 */
class PaymentMethod extends Base
{
    /**
     * PaymentMethod constructor.
     * @param array $params
     * @throws \Zend_Exception
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = 'PaymentStripe\Model\Db\Table\PaymentMethod';
        return $this;
    }
}