<?php

namespace Fanwall\Model\Db\Table;

use Fanwall\Model\Comment as ModelComment;
use Core_Model_Db_Table as DbTable;
use Zend_Db_Expr as DbExpr;

/**
 * Class Comment
 * @package Fanwall\Model\Db\Table
 */
class Comment extends DbTable
{
    /**
     * @var string
     */
    protected $_name = "fanwall_post_comment";
    /**
     * @var string
     */
    protected $_primary = "comment_id";

    /**
     * @param $postId
     * @return ModelComment[]
     * @throws \Zend_Exception
     */
    public function findForPostId($postId)
    {
        $select = $this->_db
            ->select()
            ->from("fanwall_post_comment")
            ->join(
                "customer",
                "customer.customer_id = fanwall_post_comment.customer_id",
                [
                    "firstname",
                    "lastname",
                    "nickname",
                    "author_image" => new DbExpr("customer.image"),
                ])
        ;

        $select->where("fanwall_post_comment.post_id = ?", $postId);
        $select->where("fanwall_post_comment.is_visible = ?", 1);
        $select->order("created_at ASC");

        return $this->toModelClass($this->_db->fetchAll($select));
    }
}