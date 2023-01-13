<?php

namespace Push2\Model\Onesignal;

require_once path('/lib/onesignal/vendor/autoload.php');

/**
 * Class Message
 * @package Push2\Model\Onesignal
 */
class Message {

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $message;

    /**
     * @var string
     */
    public $big_picture;

    /**
     * @var string
     */

    public $send_after;
    /**
     * @var string
     */
    public $action_url;

    // constructor
    public function __construct($title, $message, $big_picture, $send_after, $action_url) {
        $this->title = $title;
        $this->message = $message;
        $this->big_picture = $big_picture;
        $this->send_after = $send_after;
        $this->action_url = $action_url;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function getTitle() {
        return $this->title;
    }

    public function setMessage($message) {
        $this->message = $message;
    }

    public function getMessage() {
        return $this->message;
    }

    public function setBigPicture($big_picture) {
        $this->big_picture = $big_picture;
    }

    public function getBigPicture() {
        return $this->big_picture;
    }


    public function setSendAfter($send_after) {
        $this->send_after = $send_after;
    }

    public function getSendAfter() {
        return $this->send_after;
    }

    public function setActionUrl($action_url) {
        $this->action_url = $action_url;
    }

    public function getActionUrl() {
        return $this->action_url;
    }

}