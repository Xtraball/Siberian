<?php

namespace Fanwall\Model;

use Core\Model\Base;
use Customer_Model_Customer as Customer;

/**
 * Class Answer
 * @package Fanwall\Model
 *
 * @method Db\Table\Comment getTable()
 */
class Comment extends Base
{

    /**
     * @var
     */
    protected $_customer;
    /**
     * @var
     */
    protected $_comment;

    /**
     * Answer constructor.
     * @param array $datas
     * @throws \Zend_Exception
     */
    public function __construct($datas = [])
    {
        parent::__construct($datas);
        $this->_db_table = 'Fanwall\Model\Db\Table\Comment';
    }

    /**
     * @param $postId
     * @return Comment[]
     * @throws \Zend_Exception
     */
    public function findForPostId($postId)
    {
        return $this->getTable()->findForPostId($postId);
    }
}