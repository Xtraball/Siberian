<?php

use Fanwall\Model\Approval;
use Fanwall\Model\Fanwall;
use Fanwall\Model\Post;
use Fanwall\Model\Like;
use Fanwall\Model\Comment;
use Fanwall\Model\Blocked;
use Siberian\Json;
use Siberian\Xss;
use Siberian\Exception;
use Siberian\Feature;
use Customer_Model_Customer as Customer;

/**
 * Class Fanwall_Mobile_PostController
 */
class Fanwall_Mobile_PostController extends Application_Controller_Mobile_Default
{
    /**
     * @var array
     */
    public $cache_triggers = [
        'add' => [
            'tags' => [
                //'feature_paths_valueid_#VALUE_ID#',
                //'assets_paths_valueid_#VALUE_ID#',
            ],
        ],
    ];

    public function findAllAction()
    {
        try {
            $request = $this->getRequest();
            $application = $this->getApplication();
            $session = $this->getSession();
            $customerId = $session->getCustomerId();

            $optionValue = $this->getCurrentOptionValue();
            $valueId = $optionValue->getId();
            $limit = $request->getParam('limit', 20);
            $offset = $request->getParam('offset', 0);

            $query = [
                'fanwall_post.value_id = ?' => $optionValue->getId(),
                'fanwall_post.is_visible = ?' => 1,
            ];

            // Exclude blockedUsers
            $query = Blocked::excludePosts($query, $customerId, $valueId);

            $order = [
                'fanwall_post.sticky DESC',
                'fanwall_post.date DESC',
            ];

            $limit = [
                'limit' => $limit,
                'offset' => $offset,
            ];

            $posts = (new Post())->findAllWithCustomer($query, $order, $limit);
            $postsTotal = (new Post())->findAllWithCustomer($query, $order);

            $collection = [];
            foreach ($posts as $post) {

                $comments = (new Comment())->findForPostId($post->getId());
                $commentCollection = [];
                foreach ($comments as $comment) {
                    $commentCollection[] = $comment->forJson();
                }

                // Exclude blockedUsers
                $commentCollection = Blocked::excludeComments($commentCollection, $customerId, $valueId);

                $iLiked = false;
                $likes = (new Like())->findForPostId($post->getId());
                $likeCollection = [];
                foreach ($likes as $like) {
                    $likeCollection[] = [
                        'id' => (integer) $like->getId(),
                        'customerId' => (integer) $like->getCustomerId(),
                    ];

                    if ($like->getCustomerId() == $customerId) {
                        $iLiked = true;
                    }
                }

                $author = $this->_fetchAuthor($application, $post);

                $images = array_filter(explode(',', $post->getImage()));

                $collection[] = [
                    'id' => (integer) $post->getId(),
                    'customerId' => (integer) $post->getCustomerId(),
                    'title' => (string) $post->getTitle(),
                    'subtitle' => (string) $post->getSubtitle(),
                    'text' => (string) self::removeHref(Xss::sanitize(base64_decode($post->getText()))),
                    'image' => (string) ($images[0] ?? ''),
                    'images' => $images,
                    'date' => (integer) $post->getDate(),
                    'likeCount' => (integer) $likes->count(),
                    'commentCount' => (integer) $comments->count(),
                    'latitude' => (float) $post->getLatitude(),
                    'longitude' => (float) $post->getLongitude(),
                    'isFlagged' => (boolean) $post->getFlag(),
                    'isScheduled' => (boolean) $post->getIsScheduled(),
                    'sticky' => (boolean) $post->getSticky(),
                    'iLiked' => $iLiked,
                    'isVisible' => (boolean) $post->getIsVisible(),
                    'status' => $post->getStatus(),
                    'likeLocked' => false,
                    'author' => $author,
                    'comments' => $commentCollection,
                    'likes' => $likeCollection,
                    'history' => $post->getHistoryJson(),
                    'showDistance' => false,
                ];
            }

            $payload = [
                'success' => true,
                'pageTitle' => $optionValue->getTabbarName(),
                'total' => $postsTotal->count(),
                'collection' => $collection,
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    public function findOneAction()
    {
        try {
            $request = $this->getRequest();
            $application = $this->getApplication();

            $optionValue = $this->getCurrentOptionValue();
            $valueId = $optionValue->getId();
            $postId = $request->getParam('postId', null);

            $query = [
                'fanwall_post.post_id = ?' => $postId,
                'fanwall_post.value_id = ?' => $valueId,
                'fanwall_post.is_visible = ?' => 1,
            ];

            $posts = (new Post())->findAllWithCustomer($query);
            if ($posts->count() === 0) {
                throw new Exception(p__('fanwall', 'The post you are looking for is not available!'));
            }

            $collection = [];
            foreach ($posts as $post) {

                $comments = (new Comment())->findForPostId($post->getId());
                $commentCollection = [];
                foreach ($comments as $comment) {
                    $commentCollection[] = $comment->forJson();
                }

                $likes = (new Like())->findForPostId($post->getId());
                $likeCollection = [];
                foreach ($likes as $like) {
                    $likeCollection[] = [
                        'id' => (integer) $like->getId(),
                        'customerId' => (integer) $like->getCustomerId(),
                    ];
                }

                $author = $this->_fetchAuthor($application, $post);

                $images = array_filter(explode(',', $post->getImage()));

                $collection[] = [
                    'id' => (integer) $post->getId(),
                    'customerId' => (integer) $post->getCustomerId(),
                    'title' => (string) $post->getTitle(),
                    'subtitle' => (string) $post->getSubtitle(),
                    'text' => (string) Xss::sanitize(base64_decode($post->getText())),
                    'image' => (string) ($images[0] ?? ''),
                    'images' => $images,
                    'date' => (integer) $post->getDate(),
                    'likeCount' => (integer) $likes->count(),
                    'commentCount' => (integer) $comments->count(),
                    'latitude' => (float) $post->getLatitude(),
                    'longitude' => (float) $post->getLongitude(),
                    'isFlagged' => (boolean) $post->getFlag(),
                    'isScheduled' => (boolean) $post->getIsScheduled(),
                    'sticky' => (boolean) $post->getSticky(),
                    'iLiked' => false,
                    'isVisible' => (boolean) $post->getIsVisible(),
                    'status' => $post->getStatus(),
                    'likeLocked' => false,
                    'author' => $author,
                    'comments' => $commentCollection,
                    'likes' => $likeCollection,
                    'history' => $post->getHistoryJson(),
                    'showDistance' => false,
                ];
            }

            $payload = [
                'success' => true,
                'collection' => $collection,
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    public function findAllProfileAction()
    {
        try {
            $request = $this->getRequest();
            $application = $this->getApplication();
            $session = $this->getSession();
            $customerId = $session->getCustomerId();

            $optionValue = $this->getCurrentOptionValue();
            $valueId = $optionValue->getId();
            $limit = $request->getParam('limit', 20);
            $offset = $request->getParam('offset', 0);

            $query = [
                'fanwall_post.value_id = ?' => $optionValue->getId(),
                'fanwall_post.customer_id = ?' => $customerId,
            ];

            $order = [
                'fanwall_post.sticky DESC',
                'fanwall_post.date DESC',
            ];

            $limit = [
                'limit' => $limit,
                'offset' => $offset,
            ];

            $posts = (new Post())->findAllWithCustomer($query, $order, $limit);
            $postsTotal = (new Post())->findAllWithCustomer($query, $order);

            $collection = [];
            foreach ($posts as $post) {

                $comments = (new Comment())->findForPostId($post->getId());
                $commentCollection = [];
                foreach ($comments as $comment) {
                    $commentCollection[] = $comment->forJson();
                }

                // Exclude blockedUsers
                $commentCollection = Blocked::excludeComments($commentCollection, $customerId, $valueId);

                $iLiked = false;
                $likes = (new Like())->findForPostId($post->getId());
                $likeCollection = [];
                foreach ($likes as $like) {
                    $likeCollection[] = [
                        'id' => (integer) $like->getId(),
                        'customerId' => (integer) $like->getCustomerId(),
                    ];

                    if ($like->getCustomerId() == $customerId) {
                        $iLiked = true;
                    }
                }

                $author = $this->_fetchAuthor($application, $post);

                $images = array_filter(explode(',', $post->getImage()));

                $collection[] = [
                    'id' => (integer) $post->getId(),
                    'customerId' => (integer) $post->getCustomerId(),
                    'title' => (string) $post->getTitle(),
                    'subtitle' => (string) $post->getSubtitle(),
                    'text' => (string) Xss::sanitize(base64_decode($post->getText())),
                    'image' => (string) ($images[0] ?? ''),
                    'images' => $images,
                    'date' => (integer) $post->getDate(),
                    'likeCount' => (integer) $likes->count(),
                    'commentCount' => (integer) $comments->count(),
                    'latitude' => (float) $post->getLatitude(),
                    'longitude' => (float) $post->getLongitude(),
                    'isFlagged' => (boolean) $post->getFlag(),
                    'isScheduled' => (boolean) $post->getIsScheduled(),
                    'sticky' => (boolean) $post->getSticky(),
                    'iLiked' => (boolean) $iLiked,
                    'isVisible' => (boolean) $post->getIsVisible(),
                    'status' => $post->getStatus(),
                    'likeLocked' => (boolean) false,
                    'author' => $author,
                    'comments' => $commentCollection,
                    'likes' => $likeCollection,
                    'history' => $post->getHistoryJson(),
                    'showDistance' => (boolean) false,
                ];
            }

            $payload = [
                'success' => true,
                'pageTitle' => $optionValue->getTabbarName(),
                'total' => $postsTotal->count(),
                'collection' => $collection,
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    public function findAllNearbyAction()
    {
        try {
            $request = $this->getRequest();
            $application = $this->getApplication();
            $session = $this->getSession();
            $customerId = $session->getCustomerId();

            $optionValue = $this->getCurrentOptionValue();
            $valueId = $optionValue->getId();
            $limit = $request->getParam('limit', 20);
            $offset = $request->getParam('offset', 0);
            $position = [
                'latitude' => $request->getParam('latitude', 0),
                'longitude' => $request->getParam('longitude', 0),
            ];

            // Within radius!
            $fanWall = (new Fanwall())->find($optionValue->getId(), 'value_id');
            $radius = $fanWall->getRadius();

            $query = [
                'search_by_distance' => true,
                'radius' => ($radius * 1000),
                'latitude' => $position['latitude'],
                'longitude' => $position['longitude'],
                'fanwall_post.value_id = ?' => $optionValue->getId(),
                'fanwall_post.is_visible = ?' => 1,
            ];

            // Exclude blockedUsers
            $query = Blocked::excludePosts($query, $customerId, $valueId);

            $order = [
                'fanwall_post.sticky DESC',
                'fanwall_post.date DESC',
            ];

            $limit = [
                'limit' => $limit,
                'offset' => $offset,
            ];

            $posts = (new Post())->findAllWithCustomer($query, $order, $limit);
            $postsTotal = (new Post())->findAllWithCustomer($query, $order);

            $collection = [];
            foreach ($posts as $post) {

                $comments = (new Comment())->findForPostId($post->getId());
                $commentCollection = [];
                foreach ($comments as $comment) {
                    $commentCollection[] = $comment->forJson();
                }

                // Exclude blockedUsers
                $commentCollection = Blocked::excludeComments($commentCollection, $customerId, $valueId);

                $iLiked = false;
                $likes = (new Like())->findForPostId($post->getId());
                $likeCollection = [];
                foreach ($likes as $like) {
                    $likeCollection[] = [
                        'id' => (integer) $like->getId(),
                        'customer_id' => (integer) $like->getCustomerId(),
                    ];

                    if ($like->getCustomerId() == $customerId) {
                        $iLiked = true;
                    }
                }

                $author = $this->_fetchAuthor($application, $post);

                $distance = (float) $post->getDistance();
                $distanceUnit = 'km';
                if ($distance < 1000) {
                    $distanceUnit = 'm';
                    $distance = round($distance, 0);
                } else {
                    $distance = round($distance / 1000, 2);
                }

                $images = array_filter(explode(',', $post->getImage()));

                $collection[] = [
                    'id' => (integer) $post->getId(),
                    'customerId' => (integer) $post->getCustomerId(),
                    'title' => (string) $post->getTitle(),
                    'subtitle' => (string) $post->getSubtitle(),
                    'text' => (string) Xss::sanitize(base64_decode($post->getText())),
                    'image' => (string) ($images[0] ?? ''),
                    'images' => $images,
                    'date' => (integer) $post->getDate(),
                    'likeCount' => (integer) $likes->count(),
                    'commentCount' => (integer) $comments->count(),
                    'latitude' => (float) $post->getLatitude(),
                    'longitude' => (float) $post->getLongitude(),
                    'isFlagged' => (boolean) $post->getFlag(),
                    'isScheduled' => (boolean) $post->getIsScheduled(),
                    'sticky' => (boolean) $post->getSticky(),
                    'iLiked' => (boolean) $iLiked,
                    'isVisible' => (boolean) $post->getIsVisible(),
                    'status' => $post->getStatus(),
                    'likeLocked' => (boolean) false,
                    'author' => $author,
                    'comments' => $commentCollection,
                    'likes' => $likeCollection,
                    'history' => $post->getHistoryJson(),
                    'showDistance' => (boolean) true,
                    'distance' => (float) $distance,
                    'distanceUnit' => $distanceUnit,
                    'locationShort' => (string) $post->getLocationShort(),
                ];
            }

            $payload = [
                'success' => true,
                'pageTitle' => $optionValue->getTabbarName(),
                'total' => $postsTotal->count(),
                'collection' => $collection,
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    public function findAllMapAction()
    {
        try {
            $request = $this->getRequest();
            $application = $this->getApplication();
            $session = $this->getSession();
            $customerId = $session->getCustomerId();

            $optionValue = $this->getCurrentOptionValue();
            $valueId = $optionValue->getId();
            $position = [
                'latitude' => $request->getParam('latitude', 0),
                'longitude' => $request->getParam('longitude', 0),
            ];

            // Within radius!
            $fanWall = (new Fanwall())->find($optionValue->getId(), 'value_id');
            $radius = $fanWall->getRadius();

            $query = [
                'search_by_distance' => true,
                'radius' => ($radius * 1000),
                'latitude' => $position['latitude'],
                'longitude' => $position['longitude'],
                'fanwall_post.value_id = ?' => $optionValue->getId(),
                'fanwall_post.is_visible = ?' => 1,
            ];

            // Exclude blockedUsers
            $query = Blocked::excludePosts($query, $customerId, $valueId);

            $order = [
                'fanwall_post.sticky DESC',
                'fanwall_post.date DESC',
            ];

            $posts = (new Post())->findAllWithCustomer($query, $order);
            $postsTotal = (new Post())->findAllWithCustomer($query, $order);

            $collection = [];
            foreach ($posts as $post) {

                $comments = (new Comment())->findForPostId($post->getId());
                $commentCollection = [];
                foreach ($comments as $comment) {
                    $commentCollection[] = $comment->forJson();
                }

                // Exclude blockedUsers
                $commentCollection = Blocked::excludeComments($commentCollection, $customerId, $valueId);

                $iLiked = false;
                $likes = (new Like())->findForPostId($post->getId());
                $likeCollection = [];
                foreach ($likes as $like) {
                    $likeCollection[] = [
                        'id' => (integer) $like->getId(),
                        'customer_id' => (integer) $like->getCustomerId(),
                    ];

                    if ($like->getCustomerId() == $customerId) {
                        $iLiked = true;
                    }
                }

                $author = $this->_fetchAuthor($application, $post);

                $distance = (float) $post->getDistance();
                $distanceUnit = 'km';
                if ($distance < 1000) {
                    $distance = round($distance, 0);
                    $distanceUnit = 'm';
                } else {
                    $distance = round($distance / 1000, 2);
                }

                $images = array_filter(explode(',', $post->getImage()));

                $collection[] = [
                    'id' => (integer) $post->getId(),
                    'customerId' => (integer) $post->getCustomerId(),
                    'title' => (string) $post->getTitle(),
                    'subtitle' => (string) $post->getSubtitle(),
                    'text' => (string) Xss::sanitize(base64_decode($post->getText())),
                    'image' => (string) ($images[0] ?? ''),
                    'images' => $images,
                    'date' => (integer) $post->getDate(),
                    'likeCount' => (integer) $likes->count(),
                    'commentCount' => (integer) $comments->count(),
                    'latitude' => (float) $post->getLatitude(),
                    'longitude' => (float) $post->getLongitude(),
                    'isFlagged' => (boolean) $post->getFlag(),
                    'isScheduled' => (boolean) $post->getIsScheduled(),
                    'sticky' => (boolean) $post->getSticky(),
                    'iLiked' => (boolean) $iLiked,
                    'isVisible' => (boolean) $post->getIsVisible(),
                    'status' => $post->getStatus(),
                    'likeLocked' => (boolean) false,
                    'author' => $author,
                    'comments' => $commentCollection,
                    'likes' => $likeCollection,
                    'history' => $post->getHistoryJson(),
                    'showDistance' => (boolean) true,
                    'distance' => (float) $distance,
                    'distanceUnit' => $distanceUnit,
                    'locationShort' => (string) $post->getLocationShort(),
                ];
            }

            $groups = [];
            foreach ($collection as $currentItem) {
                $latLngShort = round($currentItem['latitude'], 4) . '_' . round($currentItem['longitude'], 4);
                if ($latLngShort == '0_0') {
                    continue;
                }

                if (!array_key_exists($latLngShort, $groups)) {
                    $groups[$latLngShort] = [];
                }
                $groups[$latLngShort][] = $currentItem;
            }

            $payload = [
                'success' => true,
                'pageTitle' => $optionValue->getTabbarName(),
                'total' => $postsTotal->count(),
                'collection' => $groups,
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    public function findAllBlockedAction()
    {
        try {
            $session = $this->getSession();
            $customerId = $session->getCustomerId();
            $optionValue = $this->getCurrentOptionValue();

            $collection = [];
            $blocked = (new Blocked())->find([
                'customer_id' => $customerId,
                'value_id' => $optionValue->getId()
            ]);
            if ($blocked->getId()) {
                try {
                    $userIds = Json::decode($blocked->getBlockedUsers());
                } catch (\Exception $e) {
                    $userIds = [];
                }
                
                if (count($userIds) > 0) {
                    $users = (new Customer())->findAll(['customer_id IN (?)' => $userIds]);
                    
                    foreach ($users as $user) {
                        $collection[] = [
                            'id' => (string) $user->getId(),
                            'firstname' => (string) $user->getFirstname(),
                            'lastname' => (string) $user->getLastname(),
                            'nickname' => (string) $user->getNickname(),
                            'image' => (string) $user->getImage(),
                        ];
                    }
                }
            }

            $payload = [
                'success' => true,
                'collection' => $collection,
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    public function loadSettingsAction()
    {
        try {
            $optionValue = $this->getCurrentOptionValue();
            $fanWall = (new Fanwall())->find($optionValue->getId(), 'value_id');
            $settings = $fanWall->buildSettings();
            $payload = [
                'success' => true,
                'settings' => $settings,
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    public function likePostAction()
    {
        try {
            $request = $this->getRequest();
            $session = $this->getSession();

            if (!$session->isLoggedIn()) {
                throw new Exception('You must be logged-in to like a post.');
            }

            $customerId = $session->getCustomerId();
            $postId = $request->getParam('postId', null);

            $post = (new Post())->find($postId);
            if (!$post->getId()) {
                throw new Exception("This post doesn't exists.");
            }

            $headers = [
                'user-agent' => $request->getHeader('User-Agent'),
                'forwarded-for' => $request->getHeader('X-Forwarded-For'),
                'remote-addr' => $request->getServer('REMOTE_ADDR'),
            ];

            $post->like($customerId, $headers);

            $payload = [
                'success' => true,
                'message' => 'You like this post.',
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    public function unlikePostAction()
    {
        try {
            $request = $this->getRequest();
            $session = $this->getSession();

            if (!$session->isLoggedIn()) {
                throw new Exception('You must be logged-in to unlike a post.');
            }

            $customerId = $session->getCustomerId();

            $postId = $request->getParam('postId', null);

            $post = (new Post())->find($postId);
            if (!$post->getId()) {
                throw new Exception("This post doesn't exists.");
            }

            $post->unlike($customerId);

            $payload = [
                'success' => true,
                'message' => 'You unlike this post.',
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    public function sendPostAction()
    {
        try {
            $request = $this->getRequest();
            $session = $this->getSession();
            $optionValue = $this->getCurrentOptionValue();
            $valueId = $optionValue->getId();
            $values = $request->getBodyParams();
            $fanWall = (new Fanwall())->find($valueId, 'value_id');
            $settings = $fanWall->buildSettings();

            if (!$session->isLoggedIn()) {
                throw new Exception('You must be logged-in to create a post.');
            }

            $customerId = $session->getCustomerId();
            $postId = $values['postId'];
            $form = $values['form'];
            $text = base64_encode(preg_replace('/[\n\r]/m', '', nl2br($form['text'])));
            $picture = $form['picture'];
            $pictures = $form['pictures'];
            $date = $form['date'];

            // Tries to find post if 'edit'
            $post = (new Post())->find($postId);
            if (!$post || !$post->getId()) {
                $post = new Post();
            }

            $saveToHistory = false;
            $archivedPost = null;
            if ($post->getId()) {
                $saveToHistory = true;
                $archivedPost = [
                    'id' => (integer) $post->getId(),
                    'customerId' => (integer) $post->getCustomerId(),
                    'title' => (string) $post->getTitle(),
                    'subtitle' => (string) $post->getSubtitle(),
                    'text' => (string) $post->getText(),
                    'image' => (string) $post->getImage(),
                    'date' => (integer) $post->getDate(),
                    'latitude' => (float) $post->getLatitude(),
                    'longitude' => (float) $post->getLongitude(),
                    'locationShort' => (string) $post->getLocationShort(),
                ];
            }

            $headers = [
                'user-agent' => $request->getHeader('User-Agent'),
                'forwarded-for' => $request->getHeader('X-Forwarded-For'),
                'remote-addr' => $request->getServer('REMOTE_ADDR'),
            ];

            // Strip unwanted tags
            $text = strip_tags($text, '<p><em><s><b><strong><u><span><h1><h2>');

            $post
                ->setValueId($optionValue->getId())
                ->setCustomerId($customerId)
                ->setDate($date)
                ->setText($text)
                ->setUserAgent($headers['user_agent'])
                ->setCustomerIp($headers['forwarded-for'] . ', ' . $headers['remote-addr'])
                ->setIsVisible(true);

            if (array_key_exists('location', $form) &&
                $form['location']['latitude'] != 0 &&
                $form['location']['longitude'] != 0) {
                $post
                    ->setLatitude($form['location']['latitude'])
                    ->setLongitude($form['location']['longitude'])
                    ->setLocationShort($form['location']['locationShort']);
            }

            // Pre 4.18.5 handling a single picture
            if (mb_strlen($picture) > 0) {
                if (preg_match('#^/#', $picture) === 1) {
                    // Not changing image!
                } else {
                    // Save base64 image to file
                    $uniqId = uniqid('fwimg_', true);
                    $tmpPath = path("/var/tmp/{$uniqId}");
                    $imagePath = base64imageToFile($picture, $tmpPath);
                    $finalPath = Feature::saveImageForOption($optionValue, $imagePath);
                    $post->setImage($finalPath);
                }

            } else if (count($pictures) > 0) {
                // Post 4.18.5 handling a 1-N pictures
                $imagesContainer = [];
                foreach ($pictures as $_picture) {
                    if (preg_match('#^/#', $_picture) === 1) {
                        // Not changing existing image!
                        $imagesContainer[] = $_picture;
                    } else {
                        // Save base64 image to file
                        $uniqId = uniqid('fwimg_', true);
                        $tmpPath = path("/var/tmp/{$uniqId}");
                        $imagePath = base64imageToFile($_picture, $tmpPath);
                        $finalPath = Feature::saveImageForOption($optionValue, $imagePath);

                        $imagesContainer[] = $finalPath;
                    }
                }
                // Remove image!
                $post->setImage(implode_polyfill(',', $imagesContainer));
            } else {
                // Remove image!
                $post->setImage('');
            }

            // All done, time for moderation
            $isVisible = true;
            if ($settings['enable_moderation']) {
                $isVisible = false;

                $post
                    ->setStatus('pending')
                    ->setIsVisible($isVisible)
                    ->save();
            } else {
                $post
                    ->setStatus('published')
                    ->setIsVisible(true)
                    ->save();
            }

            // Ok everything good, we can insert archive if edit
            if ($saveToHistory) {
                try {
                    $history = Json::decode($post->getHistory());
                } catch (\Exception $e) {
                    $history = [];
                }

                $history[] = $archivedPost;

                $post
                    ->setHistory(Json::encode($history))
                    ->save();
            }

            $message = p__('fanwall', 'Your post is published!');
            if ($settings['enable_moderation']) {
                $message = p__('fanwall', 'Your post is awaiting admin approval!');

                // Saving approval to history (one line per post only)
                $approval = (new Approval())->find($post->getId(), 'post_id');
                $approval
                    ->setPostId($post->getId())
                    ->setValueId($valueId)
                    ->save();
            }

            $payload = [
                'success' => true,
                'message' => $message,
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
                'trace' => $e->getTrace(),
            ];
        }

        $this->_sendJson($payload);
    }

    public function sendCommentAction()
    {
        try {
            $request = $this->getRequest();
            $session = $this->getSession();
            $optionValue = $this->getCurrentOptionValue();
            $valueId = $optionValue->getId();
            $values = $request->getBodyParams();

            if (!$session->isLoggedIn()) {
                throw new Exception('You must be logged-in to comment a post.');
            }

            $customerId = $session->getCustomerId();
            $postId = $values['postId'];
            $commentId = $values['commentId'];
            $form = $values['form'];
            $text = base64_encode(preg_replace('/[\n\r]/m', '', nl2br($form['text'])));
            $picture = $form['picture'];
            $date = $form['date'];

            $post = (new Post())->find($postId);
            if (!$post->getId()) {
                throw new Exception('The post you are trying to comment is not available.');
            }

            $comment = (new Comment())->find($commentId);

            $saveToHistory = false;
            $archivedComment = null;
            if ($comment->getId()) {
                $saveToHistory = true;
                $archivedComment = [
                    'id' => (integer) $comment->getId(),
                    'customerId' => (integer) $comment->getCustomerId(),
                    'text' => (string) $comment->getText(),
                    'image' => (string) $comment->getPicture(),
                    'date' => (integer) $comment->getDate(),
                ];
            }

            $headers = [
                'user-agent' => $request->getHeader('User-Agent'),
                'forwarded-for' => $request->getHeader('X-Forwarded-For'),
                'remote-addr' => $request->getServer('REMOTE_ADDR'),
            ];

            // Strip unwanted tags
            $text = strip_tags($text, '<p><em><s><b><strong><u><span><h1><h2>');

            $comment
                ->setPostId($postId)
                ->setCustomerId($customerId)
                ->setText($text)
                ->setDate($date)
                ->setUserAgent($headers['user_agent'])
                ->setCustomerIp($headers['forwarded-for'] . ', ' . $headers['remote-addr'])
                ->setIsVisible(true);


            if (mb_strlen($picture) > 0) {
                if (preg_match('#^/#', $picture) === 1) {
                    // Not changing image!
                } else {
                    // Save base64 image to file
                    $uniqId = uniqid('fwimg_', true);
                    $tmpPath = path("/var/tmp/{$uniqId}");
                    $imagePath = base64imageToFile($picture, $tmpPath);
                    $finalPath = Feature::saveImageForOption($optionValue, $imagePath);
                    $comment->setPicture($finalPath);
                }
            } else {
                // Remove image!
                $comment->setPicture('');
            }

            $comment->save();

            // Ok everything good, we can insert archive if edit
            if ($saveToHistory) {
                try {
                    $history = Json::decode($comment->getHistory());
                } catch (\Exception $e) {
                    $history = [];
                }

                $history[] = $archivedComment;

                $comment
                    ->setHistory(Json::encode($history))
                    ->save();
            }

            $comments = (new Comment())->findForPostId($post->getId());
            $commentCollection = [];
            foreach ($comments as $comment) {
                $commentCollection[] = $comment->forJson();
            }

            // Exclude blockedUsers
            $commentCollection = Blocked::excludeComments($commentCollection, $customerId, $valueId);

            $payload = [
                'success' => true,
                'postId' => (integer) $postId,
                'comments' => $commentCollection,
                'message' => 'Your comment is saved!',
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function deleteCommentAction()
    {
        try {
            $request = $this->getRequest();
            $session = $this->getSession();
            $commentId = $request->getParam('commentId', null);

            if (!$session->isLoggedIn()) {
                throw new Exception('You must be logged-in to delete a comment.');
            }

            $customerId = $session->getCustomerId();

            $comment = (new Comment())->find($commentId);
            if (!$comment->getId()) {
                throw new Exception('The comment you are trying to delete is not available.');
            }

            if ($comment->getCustomerId() != $customerId) {
                throw new Exception('You are not allowed to delete this comment.');
            }

            $postId = $comment->getPostId();
            $post = (new Post())->find($postId);
            $comment->delete();

            $comments = (new Comment())->findForPostId($postId);
            $commentCollection = [];
            foreach ($comments as $comment) {
                $commentCollection[] = $comment->forJson();
            }

            // Exclude blockedUsers
            $commentCollection = Blocked::excludeComments($commentCollection, $customerId, $post->getValueId());

            $payload = [
                'success' => true,
                'comments' => $commentCollection,
                'message' => 'Your comment is deleted!',
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function blockUserAction()
    {
        try {
            $request = $this->getRequest();
            $session = $this->getSession();

            if (!$session->isLoggedIn()) {
                throw new Exception('You must be logged-in to block a user.');
            }

            $data = $request->getBodyParams();
            $optionValue = $this->getCurrentOptionValue();
            $valueId = $optionValue->getId();
            $customerId = $session->getCustomerId();
            $from = $data['from'];
            $sourceId = $data['sourceId'];

            $refresh = false;

            $blockedCustomerId = null;
            $postId = null;
            switch ($from) {
                case 'from-post':
                    $post = (new Post())->find($sourceId);
                    if (!$post->getId()) {
                        throw new Exception("This post doesn't exists.");
                    }
                    $blockedCustomerId = $post->getCustomerId();
                    $postId = $post->getId();
                    $refresh = true;
                    break;
                case 'from-comment':
                    $comment = (new Comment())->find($sourceId);
                    if (!$comment->getId()) {
                        throw new Exception("This comment doesn't exists.");
                    }
                    $blockedCustomerId = $comment->getCustomerId();
                    $postId = $comment->getPostId();
                    $refresh = true;
                    break;
            }

            $blockedUser = (new Blocked())->find([
                'customer_id' => $customerId,
                'value_id' => $optionValue->getId()
            ]);

            $blockedUserList = [];
            if ($blockedUser->getId()) {
                try {
                    $blockedUserList = Json::decode($blockedUser->getBlockedUsers());
                } catch (\Exception $e) {
                    $blockedUserList = [];
                }
            }

            $blockedUserList[] = $blockedCustomerId;

            $blockedUser
                ->setCustomerId($customerId)
                ->setValueId($optionValue->getId())
                ->setBlockedUsers(Json::encode($blockedUserList))
                ->save();

            $commentCollection = [];
            if ($refresh) {
                $comments = (new Comment())->findForPostId($postId);
                foreach ($comments as $comment) {
                    $commentCollection[] = $comment->forJson();
                }

                // Exclude blockedUsers
                $commentCollection = Blocked::excludeComments($commentCollection, $customerId, $valueId);
            }

            $payload = [
                'success' => true,
                'postId' => (integer) $postId,
                'refresh' => $refresh,
                'comments' => $commentCollection,
                'message' => 'This user posts & messages are now blocked.',
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function unblockUserAction()
    {
        try {
            $request = $this->getRequest();
            $session = $this->getSession();

            if (!$session->isLoggedIn()) {
                throw new Exception('You must be logged-in to unblock a user.');
            }

            $data = $request->getBodyParams();
            $optionValue = $this->getCurrentOptionValue();
            $valueId = $optionValue->getId();
            $customerId = $session->getCustomerId();
            $from = $data['from'];
            $sourceId = $data['sourceId'];

            $refresh = false;

            $blockedCustomerId = null;
            $postId = null;
            switch ($from) {
                case 'from-post':
                    $post = (new Post())->find($sourceId);
                    if (!$post->getId()) {
                        throw new Exception("This post doesn't exists.");
                    }
                    $blockedCustomerId = $post->getCustomerId();
                    $postId = $post->getId();
                    $refresh = true;
                    break;
                case 'from-comment':
                    $comment = (new Comment())->find($sourceId);
                    if (!$comment->getId()) {
                        throw new Exception("This comment doesn't exists.");
                    }
                    $blockedCustomerId = $comment->getCustomerId();
                    $postId = $comment->getPostId();
                    $refresh = true;
                    break;
                case 'from-user':
                    $blockedCustomerId = $sourceId;
                    $postId = null;
                    break;
            }

            $blockedUser = (new Blocked())->find([
                'customer_id' => $customerId,
                'value_id' => $optionValue->getId()
            ]);

            $blockedUserList = [];
            if ($blockedUser->getId()) {
                try {
                    $blockedUserList = Json::decode($blockedUser->getBlockedUsers());
                } catch (\Exception $e) {
                    $blockedUserList = [];
                }
            }

            // Removes customerId from blocked users!
            if (($key = array_search($blockedCustomerId, $blockedUserList)) !== false) {
                unset($blockedUserList[$key]);
            }

            $blockedUser
                ->setCustomerId($customerId)
                ->setValueId($optionValue->getId())
                ->setBlockedUsers(Json::encode($blockedUserList))
                ->save();

            $commentCollection = [];
            if ($refresh) {
                $comments = (new Comment())->findForPostId($postId);
                foreach ($comments as $comment) {
                    $commentCollection[] = $comment->forJson();
                }

                // Exclude blockedUsers
                $commentCollection = Blocked::excludeComments($commentCollection, $customerId, $valueId);
            }

            $payload = [
                'success' => true,
                'postId' => (integer) $postId,
                'refresh' => $refresh,
                'comments' => $commentCollection,
                'message' => 'This user posts & messages are now unblocked.',
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function deletePostAction()
    {
        try {
            $request = $this->getRequest();
            $session = $this->getSession();

            if (!$session->isLoggedIn()) {
                throw new Exception('You must be logged-in to delete a post.');
            }

            $data = $request->getBodyParams();
            $customerId = $session->getCustomerId();
            $postId = $data['postId'];

            $post = (new Post())->find($postId);
            if (!$post->getId()) {
                throw new Exception("This post doesn't exists.");
            }

            if ($post->getCustomerId() != $customerId) {
                throw new Exception("This post doesn't belong to you.");
            }

            // Make post invisible!
            if ($post->getStatus() === 'deleted') {
                $post->delete();
                $message = 'Your post is permanently removed.';
            } else {
                $post
                    ->setIsVisible(false)
                    ->setStatus('deleted')
                    ->save();
                $message = 'Your post is trashed.';
            }

            $payload = [
                'success' => true,
                'message' => $message,
            ];
        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * @param $application
     * @param $post
     * @return array
     */
    protected function _fetchAuthor ($application, $post)
    {
        $author = [
            'firstname' => (string) $application->getName(),
            'lastname' => '',
            'nickname' => (string) $application->getName(),
            'image' => (string) $application->getIcon(64),
        ];
        if (!empty($post->getCustomerId())) {
            if (!empty($post->getAuthorId())) {
                $author = [
                    'firstname' => (string) $post->getFirstname(),
                    'lastname' => (string) $post->getLastname(),
                    'nickname' => (string) $post->getNickname(),
                    'image' => (string) $post->getAuthorImage(),
                ];
            } else {
                $author = [
                    'firstname' => 'John',
                    'lastname' => 'DOE',
                    'nickname' => 'John',
                    'image' => '',
                ];
            }
        }

        return $author;
    }

    public static function removeHref ($text)
    {
        return preg_replace('/<a.*href="(.+)".*>.*<\/a>/im', '$1', $text);
    }
}
