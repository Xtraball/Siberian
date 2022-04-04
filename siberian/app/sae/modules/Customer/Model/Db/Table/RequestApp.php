<?php

namespace Customer\Model\Db\Table;

use Core_Model_Db_Table as DbTable;

/**
 * Class RequestApp
 * @package Customer\Model\Db\Table
 */
class RequestApp extends DbTable {

    /**
     * @var string
     */
    protected $_name = 'customer_request_app';

    /**
     * @var string
     */
    protected $_primary = 'request_id';
}