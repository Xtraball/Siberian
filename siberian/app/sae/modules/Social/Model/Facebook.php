<?php

class Social_Model_Facebook extends Core_Model_Default {

    /**
     * @var integer
     */
    const MAX_RESULTS = 5;

    /**
     * @var bool
     */
    protected $_is_cacheable = false;

    /**
     * @var string
     */
    public static $endpoint = "https://graph.facebook.com/v2.7";

    /**
     * @var string
     */
    protected $_facebook_user;

    /**
     * @var array
     */
    protected $_list = array();

    /**
     * @var string
     */
    protected $_page;

    /**
     * @var string
     */
    protected $_next_url;

    /**
     * Social_Model_Facebook constructor.
     * @param array $params
     */
    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Social_Model_Db_Table_Facebook';
        return $this;
    }

    /**
     * @return array
     */
    public function getInappStates($value_id) {

        $in_app_states = array(
            array(
                "state" => "facebook-list",
                "offline" => false,
                "params" => array(
                    "value_id" => $value_id,
                ),
            ),
        );

        return $in_app_states;
    }

    /**
     * @return array|mixed|null|string
     */
    public function getFbUser() {

        if(!$this->_facebook_user) {
            $url = parse_url($this->getData('fb_user'));
            if(is_array($url) AND !empty($url['path'])) {
                $this->_facebook_user = current(explode('/', ltrim($url['path'], '/')));
            }
            if(!$this->_facebook_user) {
                $this->_facebook_user = $this->getData('fb_user');
            }
        }

        return $this->_facebook_user;

    }

    /**
     * @param int $offset
     * @return array
     */
    public function getPosts($offset = 1) {
        $offset--;

        if(!($offset%20)) {
            $this->_page = ceil(($offset+1)/20);
        }
        else {
            $this->_page = ceil($offset/20);
        }

        if($offset >= 20) {
            $offset -= 20*($this->_page-1);
        }

        $this->_list = array();
        $this->_getFBPosts($this->getFbUser());

        return array_slice($this->_list, $offset, self::MAX_RESULTS);
    }

    /**
     * @param $post_id
     * @return Core_Model_Default
     * @throws Zend_Json_Exception
     */
    public function getPost($post_id) {

        $this->_page = 1;
        $access_token = $this->__getAccessToken();

        $url = self::buildUrl($post_id, array(
            "access_token" => $access_token,
        ));
        $post = new Core_Model_Default();
        $post_datas = file_get_contents($url);
        $post_datas = Zend_Json::decode($post_datas);

        if (!empty($post_datas)) {
            $post = $this->_prepareFbPost($post_datas);
        }

        return $post;

    }

    /**
     * @param $option
     * @return $this
     */
    public function copyTo($option) {
        $this->setId(null)->setValueId($option->getId())->save();
        return $this;
    }

    /**
     * @param $username
     * @throws Zend_Json_Exception
     */
    protected function _getFBPosts($username){

        $access_token = $this->__getAccessToken();
        $url = self::buildUrl($username, array(
            "fields" => "posts,picture",
            "access_token" => $access_token,
        ));
        $response = file_get_contents($url);
        $response = Zend_Json::decode($response);

        $this->_next_url = $response['posts']['paging']['next'];

        if (!empty($response) && !empty($response['posts']['data'])) {
            foreach ($response['posts']['data'] as $post_datas) {
                if(!isset($post_datas['type']) OR $post_datas['type'] == 'status') {
                    continue;
                }
                $this->_list[] = $this->_prepareFbPost($post_datas);
            }
        }
    }

    /**
     * @param $post_datas
     * @return Core_Model_Default
     */
    protected function _prepareFbPost($post_datas) {

        $post = new Core_Model_Default();
        $icon_path = "{$post_datas["from"]["id"]}/picture";
        $icon_url = self::buildUrl($icon_path, array(
            "access_token" => $this->__getAccessToken(),
        ));

        $count_comments = isset($post_datas["comments"]["data"]) ? count($post_datas["comments"]["data"]) : 0;
        if($count_comments == 25) {
            $count_comments = " > ".$count_comments;
        }
        $count_likes = isset($post_datas["likes"]["data"]) ? count($post_datas["likes"]["data"]) : 0;

        if($count_likes == 25) {
            $count_likes = ' > '.$count_likes;
        }

        $comments = array();
        if(!empty($post_datas['comments']['data']) && is_array($post_datas['comments']['data'])) {
            foreach($post_datas['comments']['data'] as $comment) {
                $created_at = new Zend_Date($comment['created_time'], Zend_Date::ISO_8601, new Zend_Locale('en_US'));

                $picture_path = "{$comment['from']['id']}/picture";
                $picture_url = self::buildUrl($picture_path, array(
                    "access_token" => $this->__getAccessToken(),
                ));

                $comments[] = new Core_Model_Default(array(
                    'id'            => $comment['id'],
                    'name'          => $comment['from']['name'],
                    'message'       => $comment['message'],
                    'picture'       => $picture_url,
                    'created_at'    => $created_at->toString(__("MM/dd/y hh:mm a"))
                ));
            }

        }

        $created_at = new Zend_Date($post_datas['created_time'], Zend_Date::ISO_8601, new Zend_Locale('en_US'));

        $message = '';
        if(!empty($post_datas['message'])) {
            $message = $post_datas['message'];
        } else if(!empty($post_datas['story'])) {
            $message = $post_datas['story'];
        }
        $picture        = !empty($post_datas['picture']) ? $post_datas['picture'] : '';
        $picture_url    = self::buildUrl("{$post_datas['object_id']}/picture?width=200", array(
            "access_token" => $this->__getAccessToken(),
        ));
        $picture        = !empty($post_datas['object_id']) ? $picture_url : $picture;

        $datas = array(
            'id'                => $post_datas['id'],
            'author'            => $post_datas['from']['name'],
            "avatar_url"        => $icon_url,
            'message'           => $message,
            'short_message'     => strlen($message) > 300 ? Core_Model_Lib_String::truncate($message, 300) : $message,
            'picture'           => $picture,
            'details'           => !empty($post_datas['name']) ? $post_datas['name'] : '',
            "link"              => isset($post_datas['link']) ? $post_datas['link'] : '',
            "created_at"        => $created_at->toString(__("MM/dd/y hh:mm a")),
            "comments"          => $comments,
            "nbr_of_comments"   => $count_comments,
            "nbr_of_likes"      => $count_likes
        );

        $post->setData($datas);

        return $post;
    }

    /**
     * Get all the albums belonging to a page
     *
     * @param $page_id
     * @return array
     */
    public function getAlbums($page_id) {
        $access_token = $this->getAccessToken();
        $url = self::buildUrl($page_id . '/albums', array(
            "access_token" => $access_token
        ));
        return $this->_getFBAlbums($url);
    }

    /**
     * Retrieves pages recursively
     *
     * @param $url
     * @return array
     */
    protected function _getFBAlbums($url) {
        $response = file_get_contents($url);
        $response = Zend_Json::decode($response);

        if(!$response["error"]) {
            $albums = $response["data"];
            // If there still are remaining albums repeat request
            if ($response["paging"]["next"]) {
                $albums = array_merge($albums, $this->_getFBAlbums($response["paging"]["next"]));
            }
        } else {
            $albums = false;
        }

        return $albums;
    }

    /**
     * Returns the photos belonging to an album, the after cursor is used for pagination
     *
     * @param $album_id
     * @param null $after
     * @return mixed|string
     */
    public function getPhotos($album_id, $after = null) {
        $access_token = $this->getAccessToken();
        $params = [
            'access_token' => $access_token,
            'fields' => 'images,name'
        ];
        if ($after) {
            $params['after'] = $after;
        }
        $url = self::buildUrl($album_id . '/photos', $params);
        $response = file_get_contents($url);
        $response = Siberian_Json::decode($response);

        return $response;
    }

    /**
     * Builds the first albumUrl
     *
     * @param $albumId
     * @return string
     */
    public function getAlbumUrl($albumId) {
        $currentPage = self::buildUrl($albumId . '/photos', [
            'access_token' => Core_Model_Lib_Facebook::getAppToken(),
            'fields' => 'images,name'
        ]);

        return $currentPage;
    }

    /**
     * Return the first & next page of an album if exists
     *
     * @param $url
     * @return array
     */
    public function getAlbumUrls($currentPage) {
        $response = file_get_contents($currentPage);
        $response = Siberian_Json::decode($response);

        $nextPage = false;
        if (isset($response['paging'], $response['paging']['next'])) {
            $nextPage = $response['paging']['next'];
        }

        return [
            'currentPage' => $currentPage,
            'nextPage' => $nextPage,
        ];
    }

    /**
     * Verifies and returns the page specified by page_id
     *
     * @param $page_id
     * @return mixed|string
     * @throws Exception
     */
    public function getPage($page_id) {
        $url = self::buildUrl($page_id, [
            'access_token' => Core_Model_Lib_Facebook::getAppToken()
        ]);
        $response = file_get_contents($url);
        $response = Zend_Json::decode($response);
        if (!$response) {
            throw new Siberian_Exception('Page not found');
        }
        return $response;
    }

    /**
     * @return string
     * @deprecated
     */
    public function getCacheId() {
        return 'SOCIAL_FACEBOOK_'.Core_Model_Language::getCurrentLanguage().sha1($this->getFbUser()."-".$this->_page);
    }

    /**
     * @return mixed
     * @deprecated
     */
    public function getAccessToken() {
        return Core_Model_Lib_Facebook::getAppToken();
    }

    /**
     * @return mixed
     */
    private function __getAccessToken() {
        return Core_Model_Lib_Facebook::getAppToken();
    }

    /**
     * @param array $params
     * @return string
     */
    public static function buildUrl($path, $params = array()) {
        return sprintf("%s/%s?%s", self::$endpoint, $path, http_build_query($params));
    }

    public static function __curl_get($url) {
        $request = curl_init();
        # Setting options
        curl_setopt($request, CURLOPT_URL, $url);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($request, CURLOPT_TIMEOUT, 3);
        # Call
        $result = curl_exec($request);
        # Closing connection
        curl_close($request);

        return $result;
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

            $facebook_model = new Social_Model_Facebook();
            $facebook = $facebook_model->find($value_id, "value_id");

            $dataset = array(
                "option" => $current_option->forYaml(),
                "facebook" => $facebook->getData(),
            );

            try {
                $result = Siberian_Yaml::encode($dataset);
            } catch(Exception $e) {
                throw new Exception("#089-03: An error occured while exporting dataset to YAML.");
            }

            return $result;

        } else {
            throw new Exception("#089-01: Unable to export the feature, non-existing id.");
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
            throw new Exception("#089-04: An error occured while importing YAML dataset '$path'.");
        }

        $application = $this->getApplication();
        $application_option = new Application_Model_Option_Value();

        if(isset($dataset["option"])) {
            $application_option
                ->setData($dataset["option"])
                ->unsData("value_id")
                ->unsData("id")
                ->setData('app_id', $application->getId())
                ->save()
            ;

            if(isset($dataset["facebook"])) {
                $new_facebook = new Social_Model_Facebook();
                $new_facebook
                    ->setData($dataset["facebook"])
                    ->setData("value_id", $application_option->getId())
                    ->unsData("id")
                    ->unsData("facebook_id")
                    ->save()
                ;
            }

        } else {
            throw new Exception("#089-02: Missing option, unable to import data.");
        }
    }

}
