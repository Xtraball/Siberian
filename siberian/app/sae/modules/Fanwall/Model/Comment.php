<?php

namespace Fanwall\Model;

use Core\Model\Base;
use Customer_Model_Customer as Customer;

/**
 * Class Answer
 * @package Fanwall\Model
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
     * @param $comment_id
     * @param bool $viewAll
     * @param null $posId
     * @return mixed
     */
    public function findByComment($comment_id, $viewAll = true, $posId = null)
    {
        return $this->getTable()->findByComment($comment_id, $viewAll, $posId);
    }

    /**
     * @param $comment
     * @return $this
     */
    public function setComment($comment)
    {
        $this->_comment = $comment;
        return $this;
    }

    /**
     * @return Fanwall
     * @throws \Zend_Exception
     */
    public function getPost()
    {
        if (is_null($this->_comment)) {
            $comment = new Fanwall();
            $comment->find($this->getCommentId());
            $this->_comment = $comment;
        }
        return $this->_comment;
    }

    /**
     * @return mixed
     */
    public function getCustomer()
    {
        if (is_null($this->_customer)) {
            $customer = new Customer();
            $this->_customer = $customer->find($this->getCustomerId());
        }

        return $this->_customer;
    }

    /**
     * @return array|mixed|string|null
     */
    public function isVisible()
    {
        return $this->getData("is_visible");
    }

    /**
     * @return Base
     */
    public function save()
    {
        $this->setIsVisible(1);
        return parent::save();
    }
}