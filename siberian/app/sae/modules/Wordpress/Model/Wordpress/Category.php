<?php

class Wordpress_Model_Wordpress_Category extends Core_Model_Default {

    protected $_posts = array();
    protected $_post_ids = array();

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Wordpress_Model_Db_Table_Wordpress_Category';
        return $this;
    }

    public function getPostIds() {
        return $this->_post_ids;
    }

    public function setPostIds($post_ids) {
        $this->_post_ids = $post_ids;
        return $this;
    }

    public function isValid() {
        return $this->getChildren() != null || $this->getId();
    }

//    public function addPost($post) {
//        $this->_posts[$post->getId()] = $post;
//        return $this;
//    }

}