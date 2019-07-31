<?php

namespace Fanwall\Model;

use Core\Model\Base;
use Siberian\Json;

/**
 * Class BlockedUser
 * @package Fanwall\Model
 */
class BlockedUser extends Base
{
    /**
     * Answer constructor.
     * @param array $datas
     * @throws \Zend_Exception
     */
    public function __construct($datas = [])
    {
        parent::__construct($datas);
        $this->_db_table = 'Fanwall\Model\Db\Table\BlockedUser';
    }

    /**
     * @param $query
     * @param $customerId
     * @return mixed
     * @throws \Zend_Exception
     */
    public static function excludePosts ($query, $customerId)
    {
        if (empty($customerId)) {
            return $query;
        }

        // blocked users mechanism!
        $blockedUser = (new self())->find($customerId, "customer_id");

        $blockedUserList = [];
        if ($blockedUser->getId()) {
            try {
                $blockedUserList = Json::decode($blockedUser->getBlockedUsers());
            } catch (\Exception $e) {
                $blockedUserList = [];
            }
        }

        // If we have some users blocked, we exclude them!
        if (sizeof($blockedUserList) > 0) {
            $query["(fanwall_post.customer_id NOT IN (?) OR fanwall_post.customer_id IS NULL)"] = $blockedUserList;
        }

        return $query;
    }

    /**
     * @param $comments
     * @param $customerId
     * @return mixed
     * @throws \Zend_Exception
     */
    public static function excludeComments ($comments, $customerId)
    {
        if (empty($customerId)) {
            return $comments;
        }

        // blocked users mechanism!
        $blockedUser = (new self())->find($customerId, "customer_id");

        $blockedUserList = [];
        if ($blockedUser->getId()) {
            try {
                $blockedUserList = Json::decode($blockedUser->getBlockedUsers());
            } catch (\Exception $e) {
                $blockedUserList = [];
            }
        }

        $newComments = [];
        foreach ($comments as $comment) {
            if (!in_array($comment["customerId"], $blockedUserList)) {
                $newComments[] = $comment;
            } else {
                $comment["text"] = (string) "You have blocked this user posts & comments.";
                $comment["image"] = (string) "";
                $comment["author"]["image"] = (string) "";
                $comment["isBlocked"] = (boolean) true;

                $newComments[] = $comment;
            }
        }

        return $newComments;
    }
}