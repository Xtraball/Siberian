<?php

namespace PaymentCash\Model;

use Core\Model\Base;

/**
 * Class Application
 * @package PaymentStripe\Model
 */
class Application extends Base
{
    /**
     * Application constructor.
     * @param array $params
     * @throws \Zend_Exception
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = "PaymentCash\Model\Db\Table\Application";
        return $this;
    }

    /**
     * @param null $appId
     * @return bool
     */
    public static function isAvailable($appId = null)
    {
        return true;
    }
}