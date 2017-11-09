<?php

class Wordpress_Model_Wordpress extends Core_Model_Default {

    const DISPLAYED_PER_PAGE = 15;

    protected $_category_ids;
    protected $_remote_root_category;
    protected $_remote_category_ids;
    protected $_remote_posts;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Wordpress_Model_Db_Table_Wordpress';
        return $this;
    }

    /**
     * @return array
     */
    public function getInappStates($value_id) {

        $in_app_states = array(
            array(
                "state" => "wordpress-list",
                "offline" => false,
                "params" => array(
                    "value_id" => $value_id,
                ),
            ),
        );

        return $in_app_states;
    }

    public function getCategoryIds() {

        if(!$this->_category_ids) {
            $this->_category_ids = array();
            if($this->getId()) {
                $category = new Wordpress_Model_Wordpress_Category();
                $categories = $category->findAll(array('wp_id' => $this->getId()));
                foreach($categories as $category) $this->_category_ids[] = $category->getWpCategoryId();
            }
        }

        return $this->_category_ids;

    }

    public function getRemoteCategoryIds() {

        if(!$this->_remote_category_ids) {
            $root = $this->getRemoteRootCategory();
            $this->_remote_category_ids = $this->_parseCategoryIds($root);
        }

        return $this->_remote_category_ids;

    }

    public function getRemoteRootCategory($url = '', $ids = array()) {

        if(!$this->_remote_root_category) {

            // Instancie la catégorie parent
            $this->_remote_root_category = new Wordpress_Model_Wordpress_Category(array('id' => 0));

            if(empty($url)) $url = $this->getData('url');
            if(empty($url)) return $this->_remote_root_category;

            try {
                // Envoie la requête
                $datas = $this->_sendRequest($url, array('object' => 'categories'));
            }
            catch(Exception $e) {
                $datas = array('status' => -1);
            }

            // Test si les données sont OK
            if($datas['status'] == '1') {

                // Parse les catégories
                foreach($datas['categories'] as $datas) {
                    $category = $this->_parseCategories($datas);
                    $categories[] = $category;
                }
                if(!empty($categories)) {
                    $this->_remote_root_category->setChildren($categories);
                }
            }
        }

        return $this->_remote_root_category;
    }

    public function getRemotePosts($showAll = false, $url = '', $useCache = false, $offset = 0) {


        $cache = Zend_Registry::get('cache');
        $cacheId = 'wordpress_cache_'.sha1($this->getId()).$showAll;
        $showAll = false;

        if(!$this->_remote_posts AND (!$useCache OR ($this->_remote_posts = $cache->load($cacheId)) === false)) {
            $this->_remote_posts = array();
            if ($this->getData('url') OR !empty($url)) {

                $category_ids = $this->getCategoryIds();
                $params = array('object' => 'posts');
                if (!$showAll) $params['cat_ids'] = $category_ids;

                // Envoie la requête
                $datas = $this->_sendRequest(!empty($url) ? $url : $this->getData('url'), $params);

                // Test si les données sont OK
                if ($datas['status'] == '1') {

                    foreach ($datas['posts'] as $post_datas) {

                        $post_datas['picture'] = !empty($post_datas["featured_image"]) ? $post_datas["featured_image"] : null;

                        if ($showAll AND count(array_intersect($category_ids, $post_datas['category_ids'])) == 0) {
                            $post_datas['is_hidden'] = true;
                        }

                        $first_picture = "";
                        $first_picture_src = "";

                        if (!empty($post_datas['description'])) {
                            $content = new Dom_SmartDOMDocument();
                            $content->loadHTML($post_datas['description']);
                            $content->encoding = 'utf-8';
                            //                            $content->removeChild($content->firstChild);
                            //                            $content->replaceChild($content->firstChild->firstChild, $content->firstChild);
                            $description = $content->documentElement;

                            // Traitement des images
                            $imgs = $description->getElementsByTagName('img');

                            if ($imgs->length > 0) {

                                foreach ($imgs as $img) {

                                    if ($img->getAttribute('src')) {
                                        if (empty($post_datas['picture']) AND empty($first_picture)) {
                                            $first_picture = $img;
                                            $first_picture_src = $src = $this->getUrl('Front/image/crop', array(
                                                'image' => base64_encode($img->getAttribute('src')),
                                                'width' => 640,
                                                'height' => 400
                                            ));
                                        } else {
//                                            $img->setAttribute('src', $this->getUrl('Front/image/crop', array(
//                                                'image' => base64_encode($img->getAttribute('src')),
//                                                'width' => 240,
//                                                'height' => 180
//                                            )));
                                            $img->removeAttribute("height");
                                        }
                                    }
                                }

                                if (!empty($first_picture)) {
                                    $first_picture->parentNode->removeChild($first_picture);
                                    $post_datas['picture'] = $first_picture_src;
                                }

                            }

                            if(empty($post_datas['picture'])) {
                                $post_datas['picture'] = $this->getNoImage();
                            }

                            // Traitement des iframes
                            $iframes = $description->getElementsByTagName('iframe');
                            if ($iframes->length > 0) {
                                foreach ($iframes as $iframe) {
                                    $iframe->setAttribute('width', '100%');
                                    $iframe->removeAttribute('height');
                                    //                                    if($iframe->getAttribute('width')) {}
                                }
                            }

                            $post_datas['description'] = $content->saveHTMLExact();
                            $post_datas['description'] = strip_tags($post_datas['description'], '<div><p><a><img><iframe>');
                        }

//                        $featured_image = null;
//                        if(!empty($post_datas["featured_image"])) {
//                            $featured_image = $this->getUrl('Front/image/crop', array(
//                                'image' => base64_encode($post_datas["featured_image"]),
//                                'width' => 640,
//                                'height' => 400
//                            ));
//                        }
//                        $post_datas['picture'] = $featured_image;

                        $this->_remote_posts[$post_datas['date']] = new Wordpress_Model_Wordpress_Category_Post($post_datas);
                    }

                }

                krsort($this->_remote_posts);

//                $cache->save($this->_remote_posts, $cacheId);

            }
        }

        return array_slice($this->_remote_posts, $offset, self::DISPLAYED_PER_PAGE);
    }

    public function checkModule($url = '') {

        if(!$url) $url = $this->getData('url');
        if(!$url) return false;
        $isOK = true;

        try {

            // Récupère le contenu du site Wordpress
            $client = new Zend_Http_Client($url.'?app-creator-api&object=categories', array(
                'adapter'   => 'Zend_Http_Client_Adapter_Curl',
            ));
            $response = $client->request();

            // Test s'il y a une réponse et si le module est installé
            if(!$response OR $response->getStatus() == 404) {
                throw new Exception('');
            }

            // Parse les données JSON
            $datas = Zend_Json::decode($response->getBody());
            // Test si les données sont OK
            if(!is_array($datas) OR empty($datas['status']) OR empty($datas['categories'])) {
                throw new Exception('');
            }
        }
        catch(Exception $e) {
            $isOK = false;
        }

        return $isOK;
    }

    public function getNoImage() {
        return Core_Model_Directory::getPathTo("images/application/placeholder/no-image-wordpress.png");
    }

    public function createDummyContents($option_value, $design, $category) {

        $dummy_content_xml = $this->_getDummyXml($design, $category);

        foreach ($dummy_content_xml->children() as $content) {

            $this->setUrl((string) $content->url)
                ->setValueId($option_value->getId())
                ->save()
            ;

            $category_wp = new Wordpress_Model_Wordpress_Category();
            $category_wp->setData(
                array(
                    'wp_id' => $this->getId(),
                    'wp_category_id' => 1
                )
            )
                ->save()
            ;
        }

    }

    public function copyTo($option) {

        $old_wp_id = $this->getId();
        $this->setId(null)->setValueId($option->getId())->save();

        $category = new Wordpress_Model_Wordpress_Category();
        $categories = $category->findAll(array('wp_id' => $old_wp_id));
        foreach($categories as $category) {
            $category->setId(null)->setWpId($this->getId())->save();
        }

        return $this;
    }

    protected function _parseCategories($datas) {

        $category = new Wordpress_Model_Wordpress_Category();
        $is_selected = in_array($datas['id'], $this->getCategoryIds());
        $picture = "";

        // Gestion des enfants
        if(!empty($datas['children'])) {
            $children = array();
            foreach($datas['children'] as $child_datas) {
                $child = $this->_parseCategories($child_datas);
                $children[] = $child;
            }
            current($children)->setIsFirst(true);
            end($children);
            current($children)->setIsLast(true);
            reset($children);
            $category->setChildren($children);

            $datas['is_last_level'] = false;
            unset($datas['children']);
        }
        else {
            $datas['is_last_level'] = true;
        }

        if(!empty($datas['post_ids'])) {
            $category->setPostIds($datas['post_ids']);
            unset($datas['post_ids']);
        }

        $datas['is_selected'] = $is_selected;
        $datas['picture'] = $picture;
        $category->addData($datas);

        return $category;

    }

    protected function _parseCategoryIds($category, $category_ids = array()) {

        $category_ids[] = $category->getId();
        if($category->getChildren()) {
            foreach($category->getChildren() as $child) {
                $category_ids = $this->_parseCategoryIds($child, $category_ids);
            }
        }

        return $category_ids;

    }


    protected function _sendRequest($url, $params = array()) {

        try {
            $params['app-creator-api'] = '1';
            if(!empty($params)) {
                $query = http_build_query($params);
                $url.='?'.$query;
            }

            // Récupère le contenu du site Wordpress
            $client = new Zend_Http_Client($url, array(
                'adapter'   => 'Zend_Http_Client_Adapter_Curl',
//                'curloptions' => array(CURLOPT_FOLLOWLOCATION => true),
            ));
            $response = $client->request();
        }
        catch(Exception $e) {
            $response = null;
        }

        if(!$response) {
            throw new Exception($this->_('An error occurred while accessing your Wordpress website. Please, verify the domain name %s', $url));
        }
        // Test si le module est installé
        if($response->getStatus() == 404) {
            throw new Exception($this->_("We are sorry but our Wordpress plugin hasn't been detected on your website. Please be sure it is correctly installed and activated."));
        }


        try {

            $content = $response->getBody();
            // Parse les données JSON
            $datas = Zend_Json::decode($content);

        }
        catch(Exception $e) {
            try {
                // Remove the BOMs
                $content = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $content);
                $datas = Zend_Json::decode($content);
            } catch(Exception $e) {
                $datas = array();
            }
        }

        // Test si les données sont OK
//        Zend_Debug::dump(empty($datas['status']));
//        Zend_Debug::dump(!is_array($datas) OR empty($datas['status']) OR (empty($datas['categories']) AND empty($datas['posts'])));
//        die;
        if(!is_array($datas) OR empty($datas['status']) OR (empty($datas['categories']) AND empty($datas['posts']))) {
            throw new Exception($this->_("We are sorry but no category has been detected on your Wordpress website."));
        }

        return $datas;

    }

    public function getFeaturePaths($option_value) {

        if(!$this->isCacheable()) return array();

        $paths = array();

        $params = array(
            'value_id' => $option_value->getId(),
            'offset' => 0
        );
        $paths[] = $option_value->getPath("findall", $params, false);
        $paths[] = $option_value->getPath("findall", array('value_id' => $option_value->getId()), false);

        $posts = $this->getRemotePosts(false, null, false, 0);
        foreach($posts as $post) {
            $picture_path = $post->getPicture();
            if(stripos($picture_path, "/image/crop") === false) {
                continue;
            }

            $paths[] = $picture_path;
        }

        return $paths;

    }

    /**
     * @param $option Application_Model_Option_Value
     * @return string
     * @throws Exception
     */
    public function exportAction($option, $export_type = null) {
        if($option && $option->getId()) {

            $current_option = $option;
            $value_id = $current_option->getId();

            $wordpress_model = new Wordpress_Model_Wordpress();
            $wordpress_category_model = new Wordpress_Model_Wordpress_Category();

            $wordpress = $wordpress_model->find($value_id, "value_id");
            $wordpress_data = $wordpress->getData();

            $wordpress_categories = $wordpress_category_model->findAll(array(
                "wp_id = ?" => $wordpress->getId(),
            ));

            $wordpress_categories_data = array();
            foreach($wordpress_categories as $wordpress_category) {
                $wordpress_categories_data[] = $wordpress_category->getData();
            }

            /** Find all wordpress_category */
            $dataset = array(
                "option" => $current_option->forYaml(),
                "wordpress" => $wordpress_data,
                "wordpress_categories" => $wordpress_categories_data,
            );

            try {
                $result = Siberian_Yaml::encode($dataset);
            } catch(Exception $e) {
                throw new Exception("#088-03: An error occured while exporting dataset to YAML.");
            }

            return $result;

        } else {
            throw new Exception("#088-01: Unable to export the feature, non-existing id.");
        }
    }

    /**
     * @param $path
     * @throws Exception
     */
    public function importAction($path) {
        $content = file_get_contents($path);

        try {
            $dataset = Siberian_Yaml::decode($content);
        } catch(Exception $e) {
            throw new Exception("#088-04: An error occured while importing YAML dataset '$path'.");
        }

        $application = $this->getApplication();

        $application_option = new Application_Model_Option_Value();

        $wordpress_model = new Wordpress_Model_Wordpress();

        if(isset($dataset["option"]) && isset($dataset["wordpress"])) {
            $new_application_option = $application_option
                ->setData($dataset["option"])
                ->unsData("value_id")
                ->unsData("id")
                ->setData('app_id', $application->getId())
                ->save()
            ;

            $new_value_id = $new_application_option->getId();

            /** Create Job/Options */
            if(isset($dataset["wordpress"]) && $new_value_id) {

                $new_wordpress = $wordpress_model
                    ->setData($dataset["wordpress"])
                    ->unsData("wp_id")
                    ->unsData("id")
                    ->unsData("created_at")
                    ->unsData("updated_at")
                    ->setData("value_id", $new_value_id)
                    ->save()
                ;

                /** Insert wordpress categories */
                if(isset($dataset["wordpress_categories"]) && $new_wordpress->getId()) {

                    foreach($dataset["wordpress_categories"] as $wordpress_category) {

                        $new_wordpress_category = new Wordpress_Model_Wordpress_Category();
                        $new_wordpress_category
                            ->setData($wordpress_category)
                            ->unsData("category_id")
                            ->unsData("id")
                            ->setData("wp_id", $new_wordpress->getId())
                            ->save()
                        ;
                    }

                }

            }

        } else {
            throw new Exception("#088-02: Missing option, unable to import data.");
        }
    }

}