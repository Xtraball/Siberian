<?php

namespace Fanwall\Model;

use Core\Model\Base;
use Siberian\Json;

/**
 * Class Blocked
 * @package Fanwall\Model
 */
class Blocked extends Base
{
    /**
     * @var string
     */
    protected $_db_table = Db\Table\Blocked::class;

    /**
     * @param $query
     * @param $customerId
     * @param $valueId
     * @return mixed
     * @throws \Zend_Exception
     */
    public static function excludePosts ($query, $customerId, $valueId)
    {
        if (empty($customerId)) {
            return $query;
        }

        // blocked users mechanism!
        $blocked = (new self())->find([
            'customer_id' => $customerId,
            'value_id' => $valueId
        ]);

        $blockedUserList = [];
        if ($blocked->getId()) {
            try {
                $blockedUserList = Json::decode($blocked->getBlockedUsers());
            } catch (\Exception $e) {
                $blockedUserList = [];
            }
        }

        // If we have some users blocked, we exclude them!
        if (count($blockedUserList) > 0) {
            $query["(fanwall_post.customer_id NOT IN (?) OR fanwall_post.customer_id IS NULL)"] = $blockedUserList;
        }

        return $query;
    }

    /**
     * @param $comments
     * @param $customerId
     * @param $valueId
     * @return mixed
     * @throws \Zend_Exception
     */
    public static function excludeComments ($comments, $customerId, $valueId)
    {
        if (empty($customerId)) {
            return $comments;
        }

        // blocked users mechanism!
        $blocked = (new self())->find([
            'customer_id' => $customerId,
            'value_id' => $valueId
        ]);

        $blockedUserList = [];
        if ($blocked->getId()) {
            try {
                $blockedUserList = Json::decode($blocked->getBlockedUsers());
            } catch (\Exception $e) {
                $blockedUserList = [];
            }
        }

        $newComments = [];
        foreach ($comments as $comment) {
            if (!in_array($comment['customerId'], $blockedUserList)) {
                $newComments[] = $comment;
            } else {
                $comment['text'] = 'You have blocked this user posts & comments.';
                $comment['image'] = '';
                $comment['author']['image'] = '';
                $comment['isBlocked'] = true;

                $newComments[] = $comment;
            }
        }

        return $newComments;
    }
}
