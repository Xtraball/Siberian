<?php

namespace Fanwall\Model\Db\Table;

use Core_Model_Db_Table as DbTable;
use Fanwall\Model\Approval as ModelApproval;
use Zend_Db_Expr as DbExpr;

/**
 * Class Approval
 * @package Fanwall\Model\Db\Table
 */
class Approval extends DbTable
{
    /**
     * @var string
     */
    protected $_name = 'fanwall_approval';
    /**
     * @var string
     */
    protected $_primary = 'approval_id';

    /**
     * @param $valueId
     * @return ModelApproval[]
     * @throws \Zend_Exception
     */
    public function findAllWithPost($valueId)
    {
        $select = $this->_db
            ->select()
            ->from('fanwall_approval')
            ->join(
                'fanwall_post',
                'fanwall_approval.post_id = fanwall_post.post_id',
                '*')
            ->join(
                'customer',
                'customer.customer_id = fanwall_post.customer_id',
                [
                    'firstname',
                    'lastname',
                    'nickname',
                    'author_image' => new DbExpr('customer.image'),
                ])
        ;

        $select->where('fanwall_approval.value_id = ?', $valueId);
        $select->order('approval_id ASC');

        return $this->toModelClass($this->_db->fetchAll($select));
    }
}