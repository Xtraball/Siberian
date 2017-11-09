<?php

class Comment_MobileController extends Application_Controller_Mobile_Default {

    /**
     * @var array
     */
    public $cache_triggers = array(
        "add" => array(
            "tags" => array(
                "feature_paths_valueid_#VALUE_ID#",
                "assets_paths_valueid_#VALUE_ID#",
            ),
        ),
        "addlike" => array(
            "tags" => array(
                "feature_paths_valueid_#VALUE_ID#",
                "assets_paths_valueid_#VALUE_ID#",
            ),
        ),
    );

    public function viewAction() {
        $this->forward("list");
    }

    public function listAction() {
        $this->forward('index', 'index', 'Front', $this->getRequest()->getParams());
    }

    public function templateAction() {
        $this->loadPartials($this->getFullActionName('_').'_l'.$this->_layout_id, false);
    }

    public function findallAction() {

        if($value_id = $this->getRequest()->getParam("value_id")) {

            $application    = $this->getApplication();
            $comment        = new Comment_Model_Comment();
            $comments       = $comment->findLastest($value_id);
            $color          = $application->getBlock("background")->getColor();

            $data = array(
                "news" => array(),
                "application" => array()
            );

            foreach($comments as $comment) {
                $data["news"][] = array(
                    "text"                  => cut($comment->getText(), 88),
                    "created_at"            => $comments->getFormattedCreatedAt(),
                    "number_of_comments"    => count($comment->getAnswers()),
                    "number_of_likes"       => count($comment->getLikes())
                );
            }

            $data["application"] = array(
                "icon_url"  => $application->getIconUrl(74),
                "name"      => $application->getName()
            );

            $data["picto"] = array(
                "pencil"    => $this->_getColorizedImage($this->getImage("picto/pencil.png"), $color),
                "comment"   => $this->_getColorizedImage($this->getImage("picto/comment.png"), $color),
                "like"      => $this->_getColorizedImage($this->getImage("picto/like.png"), $color),
            );

            $this->_sendJson($data);
        }

    }

    public function detailsAction() {

        if($datas = $this->getRequest()->getParams()) {

            try {
                if(empty($datas['comment_id']) OR empty($datas['option_value_id'])) {
                    throw new Exception(__('An error occurred during process. Please try again later.'));
                }

                $comment_id = $datas['comment_id'];

                $comment = new Comment_Model_Comment();
                if($comment_id != 'new') {
                    $comment->find($comment_id);
                    if(!$comment->getId() OR $comment->getValueId() != $this->getCurrentOptionValue()->getId()) {
                        throw new Exception(__('An error occurred during process. Please try again later.'));
                    }
                }
                else {
                    $comment->setId($comment_id);
                }

                $html = $this->getLayout()->addPartial('view_details', 'core_view_mobile_default', "comment/l$this->_layout_id/view/details.phtml")
                    ->setCurrentComment($comment)
                    ->toHtml()
                ;

                $html = array('html' => $html, 'title' => $this->getApplication()->getName());

            }
            catch(Exception $e) {
                $html = array('error' => 1, 'message' => $e->getMessage());
            }

            $this->_sendHtml($html);
        }

    }

    public function addAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {

                $customer_id = $this->getSession()->getCustomerId();
                if(empty($customer_id) OR empty($datas['status_id']) OR empty($datas['text'])) {
                    throw new Exception('Erreur');
                }

                $comment_id = $datas['status_id'];
                $text = $datas['text'];

                $comment = new Comment_Model_Answer();
                $comment->setCommentId($comment_id)
                    ->setCustomerId($customer_id)
                    ->setText($text)
                    ->save()
                ;

                $message = __('Your message has been successfully saved.');
                if(!$comment->isVisible()) $message .= ' ' . __('It will be visible only after validation by our team.');

                $data = array(
                    "success" => true,
                    "message" => $message
                );

            }
            catch(Exception $e) {
                $data = array(
                    "error" => true,
                    "message" => $e->getMessage()
                );
            }

            $this->_sendJson($data);
        }

    }

    public function pullmoreAction() {
        if($datas = $this->getRequest()->getParams()) {

            try {
                $comment = new Comment_Model_Comment();
                $comments = $comment->pullMore($datas['option_value_id'], $datas['pos_id'], $datas['from'], 5);

                $partial_comment = '';
                $partial_details = '';
                foreach($comments as $comment) :
                    $partial_comment .= $this->getLayout()->addPartial('comment_'.$comment->getId(), 'core_view_mobile_default', 'comment/l1/view/item.phtml')
                        ->setCurrentComment($comment)
                        ->toHtml()
                    ;
                    $partial_details .= $this->getLayout()->addPartial('comment_details_'.$comment->getId(), 'core_view_mobile_default', 'comment/l1/view/details.phtml')
                        ->setCurrentComment($comment)
                        ->toHtml()
                    ;
                endforeach;

                $data = array(
                    "success"   => true,
                    "comments"  => $partial_comment,
                    "details"   => $partial_details
                );

            } catch(Exception $e) {
                $data = array(
                    "error"     => true,
                    "message"   => $e->getMessage()
                );
            }

            $this->_sendJson($data);

        }

    }

    public function addlikeAction() {

        if($datas = $this->getRequest()->getParams()) {

            try {

                $customer_id = $this->getSession()->getCustomerId();

                $like = new Comment_Model_Like();
                $like->setCommentId($datas['id'])
                    ->setCustomerId($customer_id)
                    ->setCustomerIp($datas['ip'])
                    ->setAdminAgent($datas['ua'])
                ;

                $is_saved = $like->save($datas['id'], $customer_id, $datas['ip'], $datas['ua']);

                if($is_saved) {
                    $message = __('Your like has been successfully added');
                    $data = array(
                        "success" => true,
                        "message" => $message
                    );
                } else {
                    throw new Siberian_Exception(__('You can\'t like more than once the same news'));
                }

            }
            catch(Exception $e) {
                $data = array(
                    "error" => true,
                    "message" => $e->getMessage()
                );
            }

            $this->_sendJson($data);
        }

    }

}