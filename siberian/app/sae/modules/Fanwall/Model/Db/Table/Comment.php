<?php

namespace Fanwall\Model\Db\Table;

use Core_Model_Db_Table as DbTable;

/**
 * Class Comment
 * @package Fanwall\Model\Db\Table
 */
class Comment extends DbTable
{
    /**
     * @var string
     */
    protected $_name = "fanwall_comment";
    /**
     * @var string
     */
    protected $_primary = "comment_id";

    /**
     * @param $postId
     * @param $viewAll
     * @param null $posId
     * @return \Zend_Db_Table_Rowset_Abstract
     */
    public function findByPost($postId, $viewAll, $posId = null)
    {
        $select = $this->select()
            ->from($this->_name)
            ->join(
                "customer",
                "customer.customer_id = {$this->_name}.customer_id",
                [
                    "customer_email" => "customer.email",
                    "customer_name" => new \Zend_Db_Expr("CONCAT(customer.firstname, ' ', LEFT(customer.lastname, 1), '.')")
                ]
            )
            ->where("post_id = ?", $postId)
            ->order("created_at ASC")
            ->setIntegrityCheck(false);

        if (!$viewAll) {
            $select->where("is_visible = 1");
        }

        return $this->fetchAll($select);
    }
}