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
     * @param $postId
     * @return Like[]
     * @throws \Zend_Exception
     */
    public function findForPostId($postId)
    {
        return $this->getTable()->findForPostId($postId);
    }
}
