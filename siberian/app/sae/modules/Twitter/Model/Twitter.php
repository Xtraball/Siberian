<?php

require_once('lib' . DIRECTORY_SEPARATOR . 'Twitter' . DIRECTORY_SEPARATOR . 'twitteroauth' . DIRECTORY_SEPARATOR . 'autoload.php');
use Abraham\TwitterOAuth\TwitterOAuth;

class Twitter_Model_Twitter extends Core_Model_Default {
    const MAX_RESULTS = 10;
    const METHOD = 'GET';

    protected $twitter_connection;
    protected $timeline = "statuses/user_timeline";
    protected $info = "users/show";
    protected $params = array(
        'count' => self::MAX_RESULTS,
        'exclude_replies' => true
    );

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Twitter_Model_Db_Table_Twitter';
        return $this;
    }

    /**
     * @return array
     */
    public function getInappStates($value_id) {

        $in_app_states = array(
            array(
                "state" => "twitter-list",
                "offline" => false,
                "params" => array(
                    "value_id" => $value_id,
                ),
            ),
        );

        return $in_app_states;
    }

    /**
     * Defines twitter keys and set the requested user
     *
     * @return $this
     */
    public function defineSettings() {
        if (!defined('CONSUMER_KEY')) {
            $application = $this->getApplication();
            define('CONSUMER_KEY', $application->getTwitterConsumerKey());
            define('CONSUMER_SECRET', $application->getTwitterConsumerSecret());
            define('ACCESS_TOKEN', $application->getTwitterApiToken());
            define('ACCESS_TOKEN_SECRET', $application->getTwitterApiSecret());
        }
        if (!$this->params['screen_name']) {
            $this->addParam('screen_name', $this->getTwitterUser());
        }
        return $this;
    }

    /**
     * Returns the connection to the tweeter API
     *
     * @return TwitterOAuth
     */
    public function getConnection() {
        if (!$this->twitter_connection) {
            $this->twitter_connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);
        }
        return $this->twitter_connection;
    }

    /**
     * Adds a search param
     *
     * @param $name
     * @param $value
     * @return $this
     */
    public function addParam($name, $value) {
        if (!is_null($value)) {
            $this->params[$name] = $value;
        }
        return $this;
    }

    /**
     * Set the max_id parameter if it exists
     *
     * @param $last_id
     */
    public function setLastId($last_id) {
        if ($last_id) {
            $this->addParam('max_id', intval($last_id) - 1);
        } else {
            // When we don't send max_id, twitter sends back MAX_RESULTS-1 results. It's a strange bug in the twitter api.
            $this->params['count'] = self::MAX_RESULTS + 1;
        }
    }

    /**
     * Retrieve tweets
     *
     * @return array|object
     */
    public function getTweets() {
        $connection = $this->defineSettings()->getConnection();
        $tweets = $connection->get($this->timeline, $this->params);
        // Throw an error if there are any
        if ($tweets->errors) {
            throw new Exception($tweets->errors[0]->message, $tweets->errors[0]->code);
        }
        // otherwise return tweets
        return $tweets;
    }

    /**
     * * Return the user info
     * @param null $user
     * @return array
     * @throws Exception
     */
    public function getInfo($user = null){
        $connection = $this->defineSettings()->getConnection();

        if($user) {
            $this->params["screen_name"] = $user;
        }

        $user_info = $connection->get($this->info, $this->params);
        // Throw an error if there are any
        if ($user_info->errors) {
            throw new Exception($user_info->errors[0]->message, $user_info->errors[0]->code);
        }
        // otherwise return tweets
        return array($user_info);
    }

}
