<?php

class Comment_CustomerController extends Core_Controller_Default
{

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

                $message = $this->_('Your message has been successfully saved.');
                if(!$comment->isVisible()) $message .= ' ' . $this->_('It will be visible only after validation by our team.');
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