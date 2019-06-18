<?php

namespace Fanwall\Model;

use Core\Model\Base;

/**
 * Class Like
 * @package Fanwall\Model
 *
 * @method Db\Table\Like getTable();
 */
class Like extends Base
{

    /**
     * @var
     */
    protected $_comment;

    /**
     * Like constructor.
     * @param array $params
     * @throws \Zend_Exception
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = "Fanwall\Model\Db\Table\Like";
        return $this;
    }

    /**
     * @param $comment_id
     * @param null $pos_id
     * @return mixed
     */
    public function findByComment($comment_id, $pos_id = null)
    {
        $viewAll = true;
        return $this->getTable()->findByComment($comment_id, $pos_id);
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
     * @param $comment_id
     * @param $customer_id
     * @param $ip
     * @param $ua
     * @return bool
     */
    public function findByIp($comment_id, $customer_id, $ip, $ua)
    {
        $like = $this->getTable()->findByIp($comment_id, $customer_id, $ip, $ua);
        return $like->count() > 0;
    }

}
