<?php

use Fanwall\Model\Post;

class Fanwall_Application_AnswersController extends Application_Controller_Default
{

    public function editAction()
    {

        if ($this->getCurrentOptionValue()) {
            $postId = $this->getRequest()->getPost("post_id");
            $post = new Post();
            $post->find($postId);

            if ($post->getId() AND $post->getValueId() == $this->getCurrentOptionValue()->getId()) {

                $html = ['answers_html' => $this->getLayout()->addPartial('content', 'core_view_default', 'comment/application/edit/answers.phtml')
                    ->setCurrentPost($post)
                    ->setOptionValue($this->getCurrentOptionValue())
                    ->toHtml()
                ];
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }

    }

    public function deleteAction()
    {

        if ($this->getRequest()->getPost() AND $this->getCurrentOptionValue()) {

            try {
                $answer_id = $this->getRequest()->getPost('answer_id');
                if (!$answer_id) throw new Exception('');
                $answer = new Comment_Model_Answer();
                $answer->find($answer_id);

                if ($answer->getId() AND $answer->getComment()->getValueId() != $this->getCurrentOptionValue()->getId()) {
                    throw new Exception('');
                }

                $answer->delete();
                $html = ['success' => 1];
            } catch (Exception $e) {
                $html = [
                    'message' => $this->_('An error occurred while deleting this answer. Please try again later.'),
                    'message_button' => 1,
                    'message_loader' => 1
                ];
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }

    }

}
