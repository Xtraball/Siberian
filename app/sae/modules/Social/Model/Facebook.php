<?php

class Social_Model_Facebook extends Core_Model_Default {

    const MAX_RESULTS = 5;

    protected $_is_cachable = false;

    protected $_facebook_user;
    protected $_list = array();
    protected $_page;
    protected $_next_url;
    private static $__access_token;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Social_Model_Db_Table_Facebook';
        return $this;
    }

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

    public function getPosts($offset = 1) {
        $offset--;

        if(!($offset%20)) $this->_page = ceil(($offset+1)/20);
        else $this->_page = ceil($offset/20);

        if($offset >= 20) $offset -= 20*($this->_page-1);

        $cache = Zend_Registry::get('cache');
//        if(($this->_list = $cache->load($this->getCacheId())) === false ) {
            $this->_list = array();
            $this->_getFBPosts($this->getFbUser());
            $cache->save($this->_list, $this->getCacheId());
//        }
        return array_slice($this->_list, $offset, self::MAX_RESULTS);
    }

    public function getPost($post_id) {

        $cache = Zend_Registry::get('cache');
            $this->_page = 1;
            $access_token = $this->__getAccessToken();

            $url = "https://graph.facebook.com/v2.0/$post_id?access_token=$access_token";
            $post = new Core_Model_Default();
            $post_datas = file_get_contents($url);
            $post_datas = Zend_Json::decode($post_datas);

            if (!empty($post_datas)) {
                $post = $this->_prepareFbPost($post_datas);
            }

            $cache->save($post, 'SOCIAL_FACEBOOK_'.sha1($post_id));
//        }

        return $post;

    }

    public function copyTo($option) {
        $this->setId(null)->setValueId($option->getId())->save();
        return $this;
    }

    protected function _getFBPosts($username){

        $access_token = $this->__getAccessToken();

        $url = "https://graph.facebook.com/v2.0/$username/?fields=posts,picture&access_token=$access_token";//feeds.fields(message,name,caption,story,comments.fields(id,message,from,created_time),likes.fields(name),type,from,picture),
        $response = file_get_contents($url);
        $response = Zend_Json::decode($response);

        $this->_next_url = $response['posts']['paging']['next'];

        if (!empty($response) && !empty($response['posts']['data'])) {
            foreach ($response['posts']['data'] as $post_datas) {
                if(!isset($post_datas['type']) OR $post_datas['type'] == 'status') continue;
                $this->_list[] = $this->_prepareFbPost($post_datas);
            }
        }
    }

    protected function _prepareFbPost($post_datas) {

        $post = new Core_Model_Default();
        $icon_url = "https://graph.facebook.com/v2.0/{$post_datas['from']['id']}/picture";

        $count_comments = isset($post_datas['comments']['data'])?count($post_datas['comments']['data']):0;
        if($count_comments == 25) $count_comments = ' > '.$count_comments;
        $count_likes = isset($post_datas['likes']['data']) ? count($post_datas['likes']['data']) : 0;

        if($count_likes == 25) $count_likes = ' > '.$count_likes;

        $comments = array();
        if(!empty($post_datas['comments']['data']) && is_array($post_datas['comments']['data'])) {
            foreach($post_datas['comments']['data'] as $comment) {
                $created_at = new Zend_Date($comment['created_time'], Zend_Date::ISO_8601, new Zend_Locale('en_US'));
                $comments[] = new Core_Model_Default(array(
                    'id' => $comment['id'],
                    'name' => $comment['from']['name'],
                    'message' => $comment['message'],
                    'picture' => "https://graph.facebook.com/v2.0/{$comment['from']['id']}/picture",
                    'created_at' => $created_at->toString($this->_("MM/dd/y hh:mm a"))
                ));
            }

        }

        $created_at = new Zend_Date($post_datas['created_time'], Zend_Date::ISO_8601, new Zend_Locale('en_US'));

        $message = '';
        if(!empty($post_datas['message'])) $message = $post_datas['message'];
        else if(!empty($post_datas['story'])) $message = $post_datas['story'];
        $picture = !empty($post_datas['picture']) ? $post_datas['picture'] : '';
        $picture = !empty($post_datas['object_id']) ? "https://graph.facebook.com/v2.0/{$post_datas['object_id']}/picture?width=200" : $picture;

        $datas = array(
            'id' => $post_datas['id'],
            'author' => $post_datas['from']['name'],
            "avatar_url" => $icon_url,
            'message' => $message,
            'short_message' => strlen($message) > 300 ? Core_Model_Lib_String::truncate($message, 300) : $message,
            'picture' => $picture,
            'details' => !empty($post_datas['name']) ? $post_datas['name'] : '',
            "link"           => isset($post_datas['link']) ? $post_datas['link']:'',
            "created_at"     => $created_at->toString($this->_("MM/dd/y hh:mm a")),
            "comments"       => $comments,
            "nbr_of_comments"=> $count_comments,
            "nbr_of_likes"   => $count_likes
        );

        $post->setData($datas);

        return $post;
    }

    public function getCacheId() {
        return 'SOCIAL_FACEBOOK_'.Core_Model_Language::getCurrentLanguage().sha1($this->getFbUser()."-".$this->_page);
    }

    public function getAccessToken() {
//        if(!self::$__access_token) {

        $app_id     = Core_Model_Lib_Facebook::getAppId();
        $app_secret = Core_Model_Lib_Facebook::getSecretKey();

        $url = 'https://graph.facebook.com/oauth/access_token';
        $url .= '?grant_type=client_credentials';
        $url .= "&client_id=$app_id";
        $url .= "&client_secret=$app_secret";

        return str_replace('access_token=','',file_get_contents($url));
//            self::$__access_token = str_replace('access_token=','',file_get_contents($url));
//        }

//        return self::$__access_token;
    }

    private function __getAccessToken() {

        $app_id     = Core_Model_Lib_Facebook::getAppId();
        $app_secret = Core_Model_Lib_Facebook::getSecretKey();

        if($this->_page == 1) {
            $url = 'https://graph.facebook.com/v2.0/oauth/access_token';
            $url .= '?grant_type=client_credentials';
            $url .= "&client_id=$app_id";
            $url .= "&client_secret=$app_secret";
        } else {
            $url = $this->_next_url;
        }

        return str_replace('access_token=','',file_get_contents($url));
    }

}
