<?php

use Vnn\WpApiClient\Auth\WpBasicAuth;
use Vnn\WpApiClient\Http\GuzzleAdapter;
use Vnn\WpApiClient\WpClient;

/**
 * Class Wordpress2_Model_WordpressApi
 */
class Wordpress2_Model_WordpressApi extends Core_Model_Default
{
    /**
     * @var integer
     */
    const postsPerPage = 20;

    /**
     * @var Vnn\WpApiClient\WpClient
     */
    public $client;

    /**
     * @var Zend_Cache_Backend_File|Zend_Cache
     */
    public $cache;

    /**
     * @var bool
     */
    private $stripShortcodes = false;

    /**
     * @param $endpoint
     * @param null $login
     * @param null $password
     * @return $this
     * @throws Zend_Exception
     */
    public function init ($endpoint, $login = null, $password = null)
    {
        $this->client = new WpClient(new GuzzleAdapter(new GuzzleHttp\Client()), $endpoint);
        if (!empty($login) && !empty($password)) {
            $this->client->setCredentials(new WpBasicAuth($login, $password));
        }

        $this->cache = Zend_Registry::get("cache");

        return $this;
    }

    /**
     * @param $strip
     */
    public function setStripShortcodes ($strip)
    {
        $this->stripShortcodes = $strip;
    }

    /**
     * @return bool
     */
    public function getStripShortcodes ()
    {
        return $this->stripShortcodes;
    }

    /**
     * @return array
     */
    public function getCategories ()
    {
        // Break the loop after this count (could reach 250 categories)
        $failSafe = 5;

        $page = 1;
        $perPage = 50;
        $categories = $this->client->categories()->get(null, [
            'page' => $page,
            'per_page' => $perPage
        ]);

        $allCategories = $categories;
        while (count($categories) === $perPage && $failSafe > $page) {
            $page = $page + 1;
            try {
                $categories = $this->client->categories()->get(null, [
                    'page' => $page,
                    'per_page' => $perPage
                ]);
            } catch (Exception $e) {
                $page = 5;
                break;
            }

            $allCategories = array_merge($allCategories, $categories);
        }

        return $allCategories;
    }

    /**
     * @return array
     */
    public function getAllPages ()
    {
        // Break the loop after this count (could reach 250 categories)
        $failSafe = 5;

        $page = 1;
        $perPage = 50;
        try {
            $pages = $this->client->pages()->get(null, [
                'page' => $page,
                'per_page' => $perPage
            ]);
        } catch (Exception $e) {
            return [];
        }

        $allPages = $pages;
        while (count($pages) === $perPage && $failSafe > $page) {
            $page = $page + 1;
            try {
                $pages = $this->client->pages()->get(null, [
                    'page' => $page,
                    'per_page' => $perPage
                ]);
            } catch (Exception $e) {
                $page = 5;
                break;
            }

            $allPages = array_merge($allPages, $pages);
        }

        return $allPages;
    }

    /**
     * @param $categoryId
     * @param int $page
     * @param array $params
     * @return array
     */
    public function getPosts ($categoryId, $page = 1, $params = [])
    {
        /**
         * Params to implement for the user to filter posts
         *
         * https://developer.wordpress.org/rest-api/reference/posts/#list-posts
         *
         * search	Limit results to those matching a string.
         * after	Limit response to posts published after a given ISO8601 compliant date.
         * before	Limit response to posts published before a given ISO8601 compliant date.
         * author	Limit result set to posts assigned to specific authors.
         * author_exclude	Ensure result set excludes posts assigned to specific authors.
         * orderby	Sort collection by object attribute.
                    Default: date

                    One of: author, date, id, include, modified, parent, relevance, slug, title
         * status	Limit result set to posts assigned one or more statuses.
                    Default: publish
         * tags	Limit result set to all items that have the specified term assigned in the tags taxonomy.
         * tags_exclude	Limit result set to all items except those that have the specified term assigned in the tags taxonomy.
         */

        try {
            $posts = $this->client->posts()->get(null, [
                    'categories' => $categoryId,
                    'page' => $page,
                    'per_page' => self::postsPerPage
                ] + $params);
        } catch (Exception $e) {
            return [];
        }

        $allowedKeys = [
            'id',
            'date',
            'slug',
            'link',
            'title',
            'subtitle',
            'content',
            'media',
            'thumbnail',
            'picture',
        ];

        $cachedMedias = $this->getMedias($posts);

        foreach ($posts as &$post) {
            $post['title'] = $post['title']['rendered'];
            $post['subtitle'] = $this->process($post['excerpt']['rendered']);
            $post['content'] = $this->process(str_replace(
                [
                    'data-src='
                ],
                [
                    'src='
                ],
                $post['content']['rendered']
            ));

            if ($post['featured_media'] != 0) {
                try {
                    $mediaId = $post['featured_media'];
                    if (array_key_exists($mediaId, $cachedMedias)) {
                        $post["thumbnail"] = $cachedMedias[$mediaId]["thumbnail"];
                        $post["picture"] = $cachedMedias[$mediaId]["picture"];
                    } else {
                        $post["thumbnail"] = null;
                        $post["picture"] = null;
                    }
                } catch (Exception $e) {
                    $post['thumbnail'] = null;
                    $post['picture'] = null;
                }
            } else {
                $post['thumbnail'] = null;
                $post['picture'] = null;
            }

            $post = array_filter(
                $post,
                function ($key) use ($allowedKeys) {
                    return in_array($key, $allowedKeys);
                },
                ARRAY_FILTER_USE_KEY
            );
        }

        return $posts;
    }

    /**
     * @param $pageIds
     * @param int $page
     * @param array $params
     * @return array
     */
    public function getPages ($pageIds, $page = 1, $params = [])
    {
        try {
            $pages = $this->client->pages()->get(null, [
                    'include' => $pageIds,
                    'page' => $page,
                    'per_page' => self::postsPerPage
                ] + $params);
        } catch (Exception $e) {
            return [];
        }

        $allowedKeys = [
            'id',
            'date',
            'slug',
            'link',
            'title',
            'subtitle',
            'content',
            'media',
            'thumbnail',
            'picture',
        ];

        $cachedMedias = $this->getMedias($pages);

        foreach ($pages as &$page) {
            $page['title'] = $page['title']['rendered'];
            $page['subtitle'] = $this->process($page['excerpt']['rendered']);
            $page['content'] = $this->process(str_replace(
                [
                    'data-src='
                ],
                [
                    'src='
                ],
                $page['content']['rendered']
            ));

            if ($page['featured_media'] != 0) {
                try {
                    $mediaId = $page["featured_media"];
                    if (array_key_exists($mediaId, $cachedMedias)) {
                        $page["thumbnail"] = $cachedMedias[$mediaId]["thumbnail"];
                        $page["picture"] = $cachedMedias[$mediaId]["picture"];
                    } else {
                        $page["thumbnail"] = null;
                        $page["picture"] = null;
                    }
                } catch (Exception $e) {
                    $page['thumbnail'] = null;
                    $page['picture'] = null;
                }
            } else {
                $page['thumbnail'] = null;
                $page['picture'] = null;
            }

            $page = array_filter(
                $page,
                static function ($key) use ($allowedKeys) {
                    return in_array($key, $allowedKeys);
                },
                ARRAY_FILTER_USE_KEY
            );
        }

        return $pages;
    }

    /**
     * Fetch and cache medias
     *
     * @param $posts
     * @return array
     */
    public function getMedias ($posts)
    {
        // Link media to post
        $postMediaIds = [];
        foreach ($posts as $post) {
            $postId = $post['id'];
            $mediaId = $post['featured_media'];
            $postMediaIds[$postId] = $mediaId;
        }

        // Fetch all medias
        $mediaIds = array_unique(array_values($postMediaIds));

        $medias = $this->client->media()->get(null, [
            "include" => implode_polyfill(",", $mediaIds)
        ]);

        $cachedMedias = [];
        foreach ($medias as $media) {
            $thumbnail = null;
            $picture = null;
            $mediaId = $media['id'];
            if (!empty($media['media_details']['sizes'])) {
                if (isset($media["media_details"]["sizes"]["thumbnail"])) {
                    $thumbnail = $media["media_details"]["sizes"]["thumbnail"]["source_url"];
                }
                if (isset($media["media_details"]["sizes"]["medium_large"])) {
                    $picture = $media["media_details"]["sizes"]["medium_large"]["source_url"];
                }
            } else if (!empty($media['source_url'])) {
                $thumbnail = $media['source_url'];
                $picture = $media['source_url'];
            }

            if (isset($thumbnail, $picture)) {
                $cachedMedias[$mediaId] = [
                    "thumbnail" => $thumbnail,
                    "picture" => $picture,
                ];
            }
        }

        return $cachedMedias;
    }

    /**
     * @param $text
     * @return null|string|string[]
     */
    private function process ($text)
    {
        if ($this->stripShortcodes === true) {
            return preg_replace('/\[(.*?)\]/im', '', $text);
        }
        return $text;
    }
}
