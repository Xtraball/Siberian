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
     * @param $endpoint
     * @param null $login
     * @param null $password
     */
    public function init ($endpoint, $login = null, $password = null)
    {
        $this->client = new WpClient(new GuzzleAdapter(new GuzzleHttp\Client()), $endpoint);
        if (!empty($login) && !empty($password)) {
            $this->client->setCredentials(new WpBasicAuth($login, $password));
        }

        return $this;
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
        while (sizeof($categories) === $perPage && $failSafe > $page) {
            $page = $page + 1;
            $categories = $this->client->categories()->get(null, [
                'page' => $page,
                'per_page' => $perPage
            ]);
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
        $pages = $this->client->pages()->get(null, [
            'page' => $page,
            'per_page' => $perPage
        ]);

        $allPages = $pages;
        while (sizeof($pages) === $perPage && $failSafe > $page) {
            $page = $page + 1;
            $pages = $this->client->pages()->get(null, [
                'page' => $page,
                'per_page' => $perPage
            ]);
            $allPages = array_merge($allPages, $pages);
        }

        return $allPages;
    }

    /**
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

        $posts = $this->client->posts()->get(null, [
            'categories' => $categoryId,
            'page' => $page,
            'per_page' => self::postsPerPage
        ] + $params);

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

        foreach ($posts as &$post) {
            $post['title'] = $post['title']['rendered'];
            $post['subtitle'] = $post['excerpt']['rendered'];
            $post['content'] = $post['content']['rendered'];

            if ($page['featured_media'] != 0) {
                try {
                    $media = $this->client->media()->get($post['featured_media']);
                    $post['thumbnail'] = $media['media_details']['sizes']['thumbnail']['source_url'];
                    $post['picture'] = $media['media_details']['sizes']['medium_large']['source_url'];
                } catch (Exception $e) {
                    $post['thumbnail'] = null;
                    $post['picture'] = null;
                }
            } else {
                $page['thumbnail'] = null;
                $page['picture'] = null;
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
     * @return array
     */
    public function getPages ($pageIds, $page = 1, $params = [])
    {
        $pages = $this->client->pages()->get(null, [
                'include' => $pageIds,
                'page' => $page,
                'per_page' => self::postsPerPage
            ] + $params);

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

        foreach ($pages as &$page) {
            $page['title'] = $page['title']['rendered'];
            $page['subtitle'] = $page['excerpt']['rendered'];
            $page['content'] = $page['content']['rendered'];

            if ($page['featured_media'] != 0) {
                try {
                    $media = $this->client->media()->get($page['featured_media']);
                    $page['thumbnail'] = $media['media_details']['sizes']['thumbnail']['source_url'];
                    $page['picture'] = $media['media_details']['sizes']['medium_large']['source_url'];
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
                function ($key) use ($allowedKeys) {
                    return in_array($key, $allowedKeys);
                },
                ARRAY_FILTER_USE_KEY
            );
        }

        return $pages;
    }
}