<?php

namespace Fanwall\Model\Db\Table;

use Core_Model_Db_Table as DbTable;
use Fanwall\Model\Like as ModelLike;
use Zend_Db_Expr as DbExpr;

/**
 * Class Like
 * @package Fanwall\Model\Db\Table
 */
class Like extends DbTable
{
    /**
     * @var string
     */
    protected $_name = "fanwall_post_like";

    /**
     * @var string
     */
    protected $_primary = "like_id";

    /**
     * @param $postId
     * @return ModelLike[]
     * @throws \Zend_Exception
     */
    public function findForPostId($postId)
    {
        $select = $this->_db
            ->select()
            ->from("fanwall_post_like")
        ;

        $select->where("fanwall_post_like.post_id = ?", $postId);

        return $this->toModelClass($this->_db->fetchAll($select));
    }

}