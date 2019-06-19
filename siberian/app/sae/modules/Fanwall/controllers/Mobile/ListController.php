<?php

use Fanwall\Model\Fanwall;
use Fanwall\Model\Post;
use Fanwall\Model\Like;
use Fanwall\Model\Comment;
use Siberian\Xss;
use Siberian\Exception;

/**
 * Class Fanwall_Mobile_ListController
 */
class Fanwall_Mobile_ListController extends Application_Controller_Mobile_Default
{
    /**
     * @var array
     */
    public $cache_triggers = [
        "add" => [
            "tags" => [
               //"feature_paths_valueid_#VALUE_ID#",
               //"assets_paths_valueid_#VALUE_ID#",
            ],
        ],
    ];

    public function findAllAction ()
    {
        try {
            $request = $this->getRequest();
            $session = $this->getSession();
            $customerId = $session->getCustomerId();

            $optionValue = $this->getCurrentOptionValue();
            $limit = $request->getParam("limit", 20);
            $offset = $request->getParam("offset", 0);

            $query = [
                "fanwall_post.value_id = ?" => $optionValue->getId(),
                "fanwall_post.is_visible = ?" => 1,
            ];

            $order = [
                "fanwall_post.sticky DESC",
                "fanwall_post.date DESC"
            ];

            $limit = [
                "limit" => $limit,
                "offset" => $offset,
            ];

            $posts = (new Post())->findAllWithCustomer($query, $order, $limit);
            $postsTotal = (new Post())->findAllWithCustomer($query, $order);

            $collection = [];
            foreach ($posts as $post) {

                $comments = (new Comment())->findForPostId($post->getId());
                $commentCollection = [];
                foreach ($comments as $comment) {
                    $commentCollection[] = [
                        "id" => (integer) $comment->getId(),
                        "text" => (string) Xss::sanitize($comment->getText()),
                        "isFlagged" => (boolean) $comment->getFlag(),
                        "date" => datetime_to_format($comment->getCreatedAt(), \Zend_Date::TIMESTAMP),
                        "author" => [
                            "firstname" => (string) $comment->getFirstname(),
                            "lastname" => (string) $comment->getLastname(),
                            "nickname" => (string) $comment->getnickname(),
                            "image" => (string) $comment->getAuthorImage(),
                        ],
                    ];
                }

                $iLiked = false;
                $likes = (new Like())->findForPostId($post->getId());
                $likeCollection = [];
                foreach ($likes as $like) {
                    $likeCollection[] = [
                        "id" => (integer) $like->getId(),
                        "customer_id" => (integer) $like->getCustomerId(),
                    ];

                    if ($like->getCustomerId() == $customerId) {
                        $iLiked = true;
                    }
                }

                $collection[] = [
                    "id" => (integer) $post->getId(),
                    "title" => (string) $post->getTitle(),
                    "subtitle" => (string) $post->getSubtitle(),
                    "text" => (string) Xss::sanitize($post->getText()),
                    "image" => (string) $post->getImage(),
                    "date" => datetime_to_format($post->getDate(), \Zend_Date::TIMESTAMP),
                    "likeCount" => (integer) $likes->count(),
                    "commentCount" => (integer) $comments->count(),
                    "latitude" => (float) $post->getLatitude(),
                    "longitude" => (float) $post->getLongitude(),
                    "isFlagged" => (boolean) $post->getFlag(),
                    "sticky" => (boolean) $post->getSticky(),
                    "iLiked" => (boolean) $iLiked,
                    "likeLocked" => (boolean) false,
                    "author" => [
                        "firstname" => (string) $post->getFirstname(),
                        "lastname" => (string) $post->getLastname(),
                        "nickname" => (string) $post->getnickname(),
                        "image" => (string) $post->getAuthorImage(),
                    ],
                    "comments" => $commentCollection,
                    "likes" => $likeCollection,
                ];
            }

            $payload = [
                "success" => true,
                "pageTitle" => $optionValue->getTabbarName(),
                "total" => $postsTotal->count(),
                "collection" => $collection
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    public function loadSettingsAction ()
    {
        try {
            $optionValue = $this->getCurrentOptionValue();
            $fanWall = (new Fanwall())->find($optionValue->getId(), "value_id");
            $settings = $fanWall->buildSettings();
            $payload = [
                "success" => true,
                "settings" => $settings
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    public function likePostAction ()
    {
        try {
            $request = $this->getRequest();
            $session = $this->getSession();

            if (!$session->isLoggedIn()) {
                throw new Exception(p__("fanwall", "You must be logged-in to like a post."));
            }

            $customerId = $session->getCustomerId();
            $postId = $request->getParam("postId", null);

            $post = (new Post())->find($postId);
            if (!$post->getId()) {
                throw new Exception(p__("fanwall", "This post doesn't exists."));
            }

            $headers = [
                "user-agent" => $request->getHeader("User-Agent"),
                "forwarded-for" => $request->getHeader("X-Forwarded-For"),
                "remote-addr" => $request->getServer("REMOTE_ADDR"),
            ];

            $post->like($customerId, $headers);

            $payload = [
                "success" => true,
                "message" => p__("fanwall", "You like this post."),
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    public function unlikePostAction ()
    {
        try {
            $request = $this->getRequest();
            $session = $this->getSession();

            if (!$session->isLoggedIn()) {
                throw new Exception(p__("fanwall", "You must be logged-in to unlike a post."));
            }

            $customerId = $session->getCustomerId();

            $postId = $request->getParam("postId", null);

            $post = (new Post())->find($postId);
            if (!$post->getId()) {
                throw new Exception(p__("fanwall", "This post doesn't exists."));
            }

            $post->unlike($customerId);

            $payload = [
                "success" => true,
                "message" => p__("fanwall", "You unlike this post."),
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

}