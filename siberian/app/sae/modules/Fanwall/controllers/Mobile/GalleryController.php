<?php

use Fanwall\Model\Blocked;
use Fanwall\Model\Post;

/**
 * Class Fanwall_Mobile_GalleryController
 */
class Fanwall_Mobile_GalleryController extends Application_Controller_Mobile_Default
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

            // Exclude blockedUsers
            $query = Blocked::excludePosts($query, $customerId);

            $order = [
                "fanwall_post.sticky DESC",
                "fanwall_post.date DESC"
            ];

            $limit = [
                "limit" => $limit,
                "offset" => $offset,
            ];

            $posts = (new Post())->findAllImages($query, $order, $limit);
            $imagesTotal = (new Post())->findAllImages($query, $order);

            $collection = [];
            foreach ($posts as $post) {

                $collection[] = [
                    "id" => (integer) $post->getId(),
                    "image" => (string) $post->getImage(),
                ];
            }

            $payload = [
                "success" => true,
                "pageTitle" => $optionValue->getTabbarName(),
                "total" => $imagesTotal->count(),
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
}