<?php

class Twitter_Mobile_TwitterController extends Application_Controller_Mobile_Default {

    public function listAction() {
        try {
            // Twitter used max_id for tweet lookup, we send the last seen id
            $last_id = $this->getRequest()->getParam("last_id");

            // Returns a Twitter_Model_Twitter instance
            $twitter = $this->getCurrentOptionValue()->getObject();

            // we set the last seen id
            $twitter->setLastId($last_id);

            // then retrieve tweets
            $payload = $twitter->getTweets();

        } catch (Exception $e) {
            $payload = array(
                "error"     => true,
                "message"   => __($e->getMessage()),
                "code"      => $e->getCode()
            );
        }

        $this->_sendJson($payload);
    }

    public function infoAction() {
        try {
            // Returns a Twitter_Model_Twitter instance
            $twitter = $this->getCurrentOptionValue()->getObject();

            // then retrieve tweets
            $payload = $twitter->getInfo();

        } catch (Exception $e) {
            $payload = array(
                "error"     => true,
                "message"   => __($e->getMessage()),
                "code"  => $e->getCode()
            );
        }

        $this->_sendJson($payload);
    }

}