<?php

class Social_Model_Facebook extends Core_Model_Default {

    /**
     * @var integer
     */
    const MAX_RESULTS = 5;

    /**
     * @var bool
     */
    protected $_is_cachable = false;

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
        $app_id     = Core_Model_Lib_Facebook::getAppId();
        $app_secret = Core_Model_Lib_Facebook::getSecretKey();

        $url = self::buildUrl("oauth/access_token", array(
            "grant_type" => "client_credentials",
            "client_id" => $app_id,
            "client_secret" => $app_secret,
        ));

        $content = Zend_Json::decode(self::__curl_get($url));

        return $content["access_token"];
    }

    /**
     * @return mixed
     */
    private function __getAccessToken() {

        $app_id     = Core_Model_Lib_Facebook::getAppId();
        $app_secret = Core_Model_Lib_Facebook::getSecretKey();

        if($this->_page == 1) {
            $url = self::buildUrl("oauth/access_token", array(
                "grant_type" => "client_credentials",
                "client_id" => $app_id,
                "client_secret" => $app_secret,
            ));
        } else {
            $url = $this->_next_url;
        }

        $content = Zend_Json::decode(self::__curl_get($url));

        return $content["access_token"];
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

}
