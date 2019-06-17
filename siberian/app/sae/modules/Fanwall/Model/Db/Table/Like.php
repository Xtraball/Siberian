<?php

namespace Fanwall\Model\Db\Table;

use Core_Model_Db_Table as DbTable;

/**
 * Class Like
 * @package Fanwall\Model\Db\Table
 */
class Like extends DbTable
{
    /**
     * @var string
     */
    protected $_name = "fanwall_like";
    /**
     * @var string
     */
    protected $_primary = "like_id";

    /**
     * @param $comment_id
     * @param $pos_id
     * @return \Zend_Db_Table_Rowset_Abstract
     */
    public function findByComment($comment_id, $pos_id) {

        $where = [$this->_db->quoteInto('comment_id = ?', $comment_id)];
        
        if ($pos_id) {
            $where[] = $this->_db->quoteInto('pos_id = ?', $pos_id);
        }

        $where = join(' AND ', $where);
        return $this->fetchAll($where);
    }

    /**
     * @param $comment_id
     * @param $customer_id
     * @param $ip
     * @param $ua
     * @return \Zend_Db_Table_Rowset_Abstract
     */
    public function findByIp($comment_id, $customer_id, $ip, $ua) {

        $where = [$this->_db->quoteInto('comment_id = ?', $comment_id)];
        if(!empty($customer_id)) {
            $where[] = $this->_db->quoteInto('customer_id = ?', $customer_id);
        } else {
            $where[] = $this->_db->quoteInto('customer_ip = ?', $ip);
            $where[] = $this->_db->quoteInto('user_agent = ?', $ua);
        }

        $where = join(' AND ', $where);
        return $this->fetchAll($where);
    }

}