<?php

/**
 * Class Wordpress2_Mobile_ListController
 */
class Wordpress2_Mobile_ListController extends Application_Controller_Mobile_Default
{
    /**
     *
     */
    public function findAction()
    {
        try {
            $optionValue = $this->getCurrentOptionValue();
            $valueId = $optionValue->getId();
            $request = $this->getRequest();
            $page = $request->getParam('page', 1);
            $refresh = filter_var($request->getParam('refresh', false), FILTER_VALIDATE_BOOLEAN);

            $wordpress = (new Wordpress2_Model_Wordpress())
                ->find($valueId, 'value_id');

            if (!$wordpress->getId()) {
                throw new Siberian_Exception('#33-001: ' . __('An error occured.'));
            }

            $cacheId = 'wordpress2_find_' . $valueId . '_page_' . $page;
            $cacheTag = 'wordpress2_find_' . $valueId;
            $result = $this->cache->load($cacheId);
            if (!$result || $refresh) {

                // Clear subsequents pages if pull to refresh called!
                if ($refresh) {
                    $this->cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, [
                        $cacheTag,
                        'wordpress2',
                        'findAction',
                    ]);
                }

                $wordpressData = [
                    'url' => $wordpress->getData('url'),
                    'title' => $wordpress->getData('title'),
                    'subtitle' => $wordpress->getData('subtitle'),
                    'picture' => $wordpress->getData('picture'),
                    'showTitle' => (boolean) $wordpress->getData('show_title'),
                    'showCover' => (boolean) $wordpress->getData('show_cover'),
                    'groupQueries' => (boolean) $wordpress->getData('group_queries'),
                    'cardDesign' => (boolean) $wordpress->getData('card_design'),
                    'sortType' => $wordpress->getData('sort_type'),
                    'sortOrder' => $wordpress->getData('sort_order'),
                ];

                // Fetch queries!
                $wordpressQueries = (new Wordpress2_Model_Query())
                    ->findAll(
                        [
                            'value_id' => $valueId,
                            'is_published' => 1
                        ]
                    );
                $queries = [];
                $categoryIds = [];
                $pageIds = [];
                foreach ($wordpressQueries as $wordpressQuery) {
                    $query = Siberian_Json::decode($wordpressQuery->getData('query'));
                    $queryId = $wordpressQuery->getId();
                    $queries[] = [
                        'id' => $queryId,
                        'title' => $wordpressQuery->getData('title'),
                        'subtitle' => $wordpressQuery->getData('subtitle'),
                        'picture' => $wordpressQuery->getData('picture'),
                        'thumbnail' => $wordpressQuery->getData('thumbnail'),
                        'showCover' => (boolean) $wordpressQuery->getData('show_cover'),
                        'query' => $query,
                        'position' => $wordpressQuery->getData('position'),
                    ];

                    if (is_array($query['categories']) && !empty($query['categories'])) {
                        $categoryIds[$queryId] = $query['categories'];
                    }

                    if (is_array($query['pages']) && !empty($query['pages'])) {
                        $pageIds[$queryId] = $query['pages'];
                    }
                }

                $wordpressApi = (new Wordpress2_Model_WordpressApi())
                    ->init(
                        $wordpress->getData('url'),
                        $wordpress->getData('login'),
                        $wordpress->getData('password')
                    );

                $stripShortcode = filter_var($wordpress->getData('strip_shortcode'), FILTER_VALIDATE_BOOLEAN);
                $wordpressApi->setStripShortcodes($stripShortcode);

                $posts = [];

                // Immediate fetch 20 first rows 'grouped'
                if ($wordpressData['groupQueries']) {
                    // Posts
                    $groupPostIds = [];
                    foreach ($categoryIds as $queryId => $categories) {
                        $groupPostIds += $categories;
                    }

                    $posts = [];
                    if (!empty($groupPostIds)) {
                        $posts = $wordpressApi->getPosts(
                            implode_polyfill(',', array_values($groupPostIds)),
                            $page,
                            [
                                'orderby' => $wordpressData['sortType'],
                                'order' => $wordpressData['sortOrder'],
                            ]
                        );
                    }

                    // Pages
                    $groupPageIds = [];
                    foreach ($pageIds as $queryId => $pages) {
                        $groupPageIds += $pages;
                    }

                    $pages = [];
                    if (!empty($groupPageIds)) {
                        $pages = $wordpressApi->getPages(
                            implode_polyfill(',', array_values($groupPageIds)),
                            $page,
                            [
                                'orderby' => $wordpressData['sortType'],
                                'order' => $wordpressData['sortOrder'],
                            ]
                        );
                    }

                    $posts = array_values(array_merge($posts, $pages));
                }

                $payload = [
                    'success' => true,
                    'page_title' => $optionValue->getTabbarName(),
                    'queries' => $queries,
                    'wordpress' => $wordpressData,
                    'posts' => $posts,
                ];

                $cacheLifetime = $wordpress->getData('cache_lifetime');
                if ($cacheLifetime === 'null') {
                    $cacheLifetime = null;
                }

                $this->cache->save(Siberian_Json::encode($payload), $cacheId, [
                    'wordpress2',
                    'findAction',
                    'value_id_' . $valueId,
                    $cacheTag
                ], $cacheLifetime);

                $payload['x-cache'] = 'MISS';
            } else {
                $payload = Siberian_Json::decode($result);
                $payload['x-cache'] = 'HIT';
            }

        } catch (Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function loadpostsAction()
    {
        try {
            $optionValue = $this->getCurrentOptionValue();
            $valueId = $optionValue->getId();
            $request = $this->getRequest();
            $page = $request->getParam('page', 1);
            $refresh = filter_var($request->getParam('refresh', false), FILTER_VALIDATE_BOOLEAN);
            $queryId = $request->getParam('queryId', null);

            $wordpress = (new Wordpress2_Model_Wordpress())
                ->find($valueId, 'value_id');

            if (!$wordpress->getId()) {
                throw new Siberian_Exception('#33-002: ' . __('An error occured.'));
            }

            $cacheId = 'wordpress2_loadposts_' . $valueId . '_query_' . $queryId . '_page_' . $page;
            $cacheTag = 'wordpress2_loadposts_' . $valueId . '_query_' . $queryId;
            $result = $this->cache->load($cacheId);
            if (!$result || $refresh) {

                // Clear subsequents pages if pull to refresh called!
                if ($refresh) {
                    $this->cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, [
                        $cacheTag,
                        'wordpress2',
                        'loadpostsAction',
                    ]);
                }

                // Fetch query!
                $wordpressQuery = (new Wordpress2_Model_Query())
                    ->find(
                        [
                            'query_id' => $queryId,
                            'is_published' => 1
                        ]
                    );

                if (!$wordpressQuery->getId()) {
                    throw new Siberian_Exception('#33-003: ' . __('An error occured.'));
                }

                $query = Siberian_Json::decode($wordpressQuery->getData('query'));
                $categoryIds = $query['categories'];
                $pageIds = $query['pages'];
                $queryData = [
                    'title' => $wordpressQuery->getData('title'),
                    'subtitle' => $wordpressQuery->getData('subtitle'),
                    'picture' => $wordpressQuery->getData('picture'),
                    'thumbnail' => $wordpressQuery->getData('thumbnail'),
                    'showCover' => (boolean) $wordpressQuery->getData('show_cover'),
                    'showTitle' => (boolean) $wordpressQuery->getData('show_title'),
                    'query' => $query,
                    'position' => $wordpressQuery->getData('position'),
                    'sortType' => $wordpressQuery->getData('sort_type'),
                    'sortOrder' => $wordpressQuery->getData('sort_order'),
                ];

                $wordpressData = [
                    'url' => $wordpress->getData('url'),
                    'title' => $wordpress->getData('title'),
                    'subtitle' => $wordpress->getData('subtitle'),
                    'picture' => $wordpress->getData('picture'),
                    'showTitle' => (boolean) $wordpress->getData('show_title'),
                    'showCover' => (boolean) $wordpress->getData('show_cover'),
                    'groupQueries' => (boolean) $wordpress->getData('group_queries'),
                    'cardDesign' => (boolean) $wordpress->getData('card_design'),
                ];

                $wordpressApi = (new Wordpress2_Model_WordpressApi())
                    ->init(
                        $wordpress->getData('url'),
                        $wordpress->getData('login'),
                        $wordpress->getData('password')
                    );

                $stripShortcode = filter_var($wordpress->getData('strip_shortcode'), FILTER_VALIDATE_BOOLEAN);
                $wordpressApi->setStripShortcodes($stripShortcode);

                $posts = [];
                if (!empty($categoryIds)) {
                    $posts = $wordpressApi->getPosts(
                        implode_polyfill(',', array_values($categoryIds)),
                        $page,
                        [
                            'orderby' => $queryData['sortType'],
                            'order' => $queryData['sortOrder'],
                        ]
                    );
                }
                if (empty($posts)) {
                    $posts = [];
                }

                $pages = [];
                if (!empty($pageIds)) {
                    $pages = $wordpressApi->getPages(
                        implode_polyfill(',', array_values($pageIds)),
                        $page,
                        [
                            'orderby' => $queryData['sortType'],
                            'order' => $queryData['sortOrder'],
                        ]
                    );
                }
                if (empty($pages)) {
                    $pages = [];
                }

                $posts = array_values(array_merge($posts, $pages));

                $payload = [
                    'success' => true,
                    'page_title' => $optionValue->getTabbarName(),
                    'query' => $queryData,
                    'wordpress' => $wordpressData,
                    'posts' => $posts,
                ];

                $cacheLifetime = $wordpress->getData('cache_lifetime');
                if ($cacheLifetime === 'null') {
                    $cacheLifetime = null;
                }

                $this->cache->save(Siberian_Json::encode($payload), $cacheId, [
                    'wordpress2',
                    'loadpostsAction',
                    'value_id_' . $valueId,
                    $cacheTag
                ], $cacheLifetime);

                $payload['x-cache'] = 'MISS';
            } else {
                $payload = Siberian_Json::decode($result);
                $payload['x-cache'] = 'HIT';
            }
        } catch (Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        $this->_sendJson($payload);
    }
}
