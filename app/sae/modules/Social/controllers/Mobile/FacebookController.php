<?php

class Social_Mobile_FacebookController extends Application_Controller_Mobile_Default {

    public function loadmoreAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {

                if(empty($datas['facebook_id'])) throw new Exception($this->_('An error occurred while loading the videos. Please try again later.'));
                $offset = !empty($datas['offset']) ? $datas['offset'] : 1;

                $facebook = new Social_Model_Facebook();
                $facebook->find($datas['facebook_id']);
                if(!$facebook->getId() OR $facebook->getValueId() != $this->getCurrentOptionValue()->getId()) throw new Exception($this->_('An error occurred while loading the videos. Please try again later.'));
                $posts = $facebook->getPosts($offset);
                $html = array();

                foreach($posts as $key => $post) {
                    $html[] = $this->getLayout()->addPartial('li', 'core_view_default', 'social/facebook/l1/view/item.phtml')
                        ->setCurrentOption($this->getCurrentOptionValue())
                        ->setCurrentPost($post)
                        ->toHtml()
                    ;
                }

                $html = array('html' => implode('', $html));

            }
            catch(Exception $e) {
                $html = array('error' => 1, 'message' => $e->getMessage());
            }

            $this->_sendHtml($html);

        }

    }

    public function detailsAction() {

        if($datas = $this->getRequest()->getParams()) {

            try {
                if(empty($datas['post_id']) OR empty($datas['option_value_id'])) {
                    throw new Exception($this->_('An error occurred during process. Please try again later.'));
                }

                $post_id = $datas['post_id'];
                $facebook = new Social_Model_Facebook();
                $post = $facebook->getPost($datas['post_id']);

                $html = $this->getLayout()->addPartial('view_details', 'core_view_mobile_default', "social/facebook/l$this->_layout_id/view/details.phtml")
                    ->setCurrentPost($post)
                    ->setCurrentOptionValue($this->getCurrentOptionValue())
                    ->toHtml()
                ;

                $html = array('html' => $html, 'title' => $post->getAuthor());

            }
            catch(Exception $e) {
                $html = array('error' => 1, 'message' => $e->getMessage());
            }

            $this->_sendHtml($html);
        }

    }

}