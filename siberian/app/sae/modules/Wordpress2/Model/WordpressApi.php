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
    public function init($endpoint, $login = null, $password = null)
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
        ]);

        return $posts;
    }
}