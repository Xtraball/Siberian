<?php

class Comment_Application_AnswersController extends Application_Controller_Default
{

    public function editAction() {

        if($this->getCurrentOptionValue()) {
            $comment_id = $this->getRequest()->getPost('comment_id');
            $comment = new Comment_Model_Comment();
            $comment->find($comment_id);

            if($comment->getId() AND $comment->getValueId() == $this->getCurrentOptionValue()->getId()) {

                $html = array('answers_html' => $this->getLayout()->addPartial('content', 'core_view_default', 'comment/application/edit/answers.phtml')
                    ->setCurrentComment($comment)
                    ->setOptionValue($this->getCurrentOptionValue())
                    ->toHtml()
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }

    }

    public function deleteAction() {

        if($this->getRequest()->getPost() AND $this->getCurrentOptionValue()) {

            try {
                $answer_id = $this->getRequest()->getPost('answer_id');
                if(!$answer_id) throw new Exception('');
                $answer = new Comment_Model_Answer();
                $answer->find($answer_id);

                if($answer->getId() AND $answer->getComment()->getValueId() != $this->getCurrentOptionValue()->getId()) {
                    throw new Exception('');
                }

                $answer->delete();
                $html = array('success' => 1);
            }
            catch(Exception $e) {
                $html = array(
                    'message' => $this->_('An error occurred while deleting this answer. Please try again later.'),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }

    }

}
