<?php

/**
 * Class Comment_Mobile_ViewController
 */
class Comment_Mobile_ViewController extends Application_Controller_Mobile_Default {

    /**
     * @var array
     */
    public $cache_triggers = [
        "addlike" => [
            "tags" => [
                "feature_paths_valueid_#VALUE_ID#",
                "assets_paths_valueid_#VALUE_ID#",
            ],
        ],
        "flagpost" => [
            "tags" => [
                "feature_paths_valueid_#VALUE_ID#",
                "assets_paths_valueid_#VALUE_ID#",
            ],
        ],
        "flagcomment" => [
            "tags" => [
                "feature_paths_valueid_#VALUE_ID#",
                "assets_paths_valueid_#VALUE_ID#",
            ],
        ],
    ];

    public function indexAction() {
        $this->forward('index', 'index', 'Front', $this->getRequest()->getParams());
    }

    public function templateAction() {
        $this->loadPartials($this->getFullActionName('_').'_l'.$this->_layout_id, false);
    }

    public function findAction() {

        if($value_id = $this->getRequest()->getParam('value_id') AND $comment_id = $this->getRequest()->getParam('comment_id')) {
            $application = $this->getApplication();
            $comment = new Comment_Model_Comment();
            $comment->find($comment_id);
            $option = $this->getCurrentOptionValue();

            if($comment->getId() AND $comment->getValueId() == $value_id) {

                $customer = $comment->getCustomer();

                $color = $application->getBlock('background')->getColor();
                $cleaned_message = str_replace(["\n","\r"], "", html_entity_decode(strip_tags($comment->getText()), ENT_QUOTES, 'UTF-8'));

                $answer = new Comment_Model_Answer();
                $answers = $answer->findByComment($comment->getId());
                $all_answers = [];
                foreach($answers as $answer) {
                    $all_answers[] = [
                        "id"                => (integer) $answer->getId(),
                        "name"              => $answer->getCustomerName(),
                        "customer_id"       => (integer) $answer->getCustomerId(),
                        "message"           => \Siberian\Xss::sanitize($answer->getText()),
                        "created_at"        => $this->_durationSince($answer->getCreatedAt())
                    ];

                }

                switch($this->getCurrentOptionValue()->getLayoutId()) {
                    case 2:
                        $data = [
                            "id"                        => (integer) $comment->getId(),
                            "author"                    => $customer->getFirstname() ? $customer->getFirstname() : $application->getName(),
                            "title"                     => \Siberian\Xss::sanitize($comment->getTitle()),
                            "subtitle"                  => \Siberian\Xss::sanitize($comment->getSubtitle()),
                            "message"                   => \Siberian\Xss::sanitize($comment->getText()),
                            "cleaned_message"           => mb_strlen($cleaned_message) > 67 ? mb_substr($cleaned_message, 0, 64) . "..." : $cleaned_message,
                            "picture"                   => $comment->getImageUrl() ? $this->getRequest()->getBaseUrl().$comment->getImageUrl() : null,
                            "icon"                      => $this->getRequest()->getBaseUrl().$application->getIcon(74),
                            "created_at"                => $comment->getFormattedDate(__("MM.dd.y")),
                            "code"                      => $this->getCurrentOptionValue()->getCode(),
                            "social_sharing_active"     => (boolean) $option->getSocialSharingIsActive(),
                            "answers"                   => $all_answers
                        ];
                    break;
                    case 1:
                    default:
                        $data = [
                            "id"                            => (integer) $comment->getId(),
                            "author"                        => $customer->getFirstname() ? $customer->getFirstname() : $application->getName(),
                            "message"                       => \Siberian\Xss::sanitize($comment->getText()),
                            "cleaned_message"               => mb_strlen($cleaned_message) > 67 ? mb_substr($cleaned_message, 0, 64) . "..." : $cleaned_message,
                            "picture"                       => $comment->getImageUrl() ? $this->getRequest()->getBaseUrl().$comment->getImageUrl() : null,
                            "icon"                          => $this->getRequest()->getBaseUrl().$application->getIcon(74),
                            "can_comment"                   => true,
                            "created_at"                    => $this->_durationSince($comment->getCreatedAt()),
                            "number_of_likes"               => count($comment->getLikes()),
                            "flag_icon"                     => $this->_getColorizedImage($this->_getImage("pictos/flag.png"), $color),
                            "code"                          => $this->getCurrentOptionValue()->getCode(),
                            "social_sharing_active"         => (boolean) $option->getSocialSharingIsActive(),
                            "answers"                       => $all_answers
                        ];
                    break;
                }

                $this->_sendJson($data);
            }

        }

    }

    public function addlikeAction() {

        if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {

                $session = $this->getSession();
                $customer_id = $session->getCustomerId();
                $customer = new Customer_Model_Customer();
                $customer->find($customer_id);
                if(!$customer->getId()) {
                    throw new Siberian_Exception(__("You must be logged in to like something!"));
                }

                $ip = md5($_SERVER['REMOTE_ADDR']);
                $ua = md5($_SERVER['HTTP_USER_AGENT']);
                $like = new Comment_Model_Like();

                if(!$like->findByIp($data['comment_id'], $customer_id, $ip, $ua)) {

                    $like->setCommentId($data['comment_id'])
                        ->setCustomerId($customer_id)
                        ->setCustomerIp($ip)
                        ->setAdminAgent($ua)
                    ;

                    $like->save();

                    $message = __('Your like has been successfully added');
                    $html = [
                        'success' => true,
                        'message' => $message
                    ];

                } else {
                    throw new Exception(__("You can't like more than once the same news"));
                }

            }
            catch(Exception $e) {
                $html = ['error' => true, 'message' => $e->getMessage()];
            }

            $this->_sendJson($html);
        }

    }

    public function flagpostAction() {

        if($value_id = $this->getRequest()->getParam('value_id') AND $comment_id = $this->getRequest()->getParam('comment_id')) {
            $application = $this->getApplication();
            $comment = new Comment_Model_Comment();
            $comment->find($comment_id);

            if($comment->getId() AND $comment->getValueId() == $value_id) {

                $comment->setFlag($comment->getFlag() + 1);
                $comment->save();

                $message = __('Your flag has successfully been notified');
                $html = ['success' => true, 'message' => $message];

                $this->_sendJson($html);
            }
        }
    }

    public function flagcommentAction() {

        if($value_id = $this->getRequest()->getParam('value_id') AND $answer_id = $this->getRequest()->getParam('answer_id')) {
            $application = $this->getApplication();
            $answer = new Comment_Model_Answer();
            $answer->find($answer_id);

            $comment = new Comment_Model_Comment();
            $comment->find($answer->getCommentId());

            if($answer->getId() AND $comment->getValueId() == $value_id) {

                $answer->setFlag($answer->getFlag() + 1);
                $answer->save();

                $message = __('Your flag has successfully been notified');
                $html = ['success' => true, 'message' => $message];

                $this->_sendJson($html);
            }
        }
    }


}