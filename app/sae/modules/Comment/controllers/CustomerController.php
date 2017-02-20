<?php

/**
 * @deprecated
 *
 * Seems this controller is not used anymore
 *
 * Class Comment_CustomerController
 */
class Comment_CustomerController extends Core_Controller_Default
{

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
    );

    public function listAction() {
        $this->loadPartials();
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

                $comment = new Comment_Model_Customer_Answer();
                $comment->setCommentId($comment_id)
                    ->setCustomerId($customer_id)
                    ->setText($text)
                    ->save()
                ;

                $message = __('Your message has been successfully saved.');
                if(!$comment->isVisible()) $message .= ' ' . __('It will be visible only after validation by our team.');
                if($this->getCurrentAdmin()->getDesignId() == 6) {
                    $html = array('success' => 1, 'message' => $message);
                }
                else {
                    $this->getLayout()
                        ->setBaseRender('content', 'comment/list.phtml', 'comment_view_pos_list')
                        ->setMessage($message)
                    ;
                    $html = array('html' => $this->getLayout()->render());
                }

            }
            catch(Exception $e) {
                $html = array('message' => $e->getMessage());
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }

    }

}