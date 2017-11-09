<?php

class Wordpress_MobileController extends Application_Controller_Mobile_Default
{

    public function viewAction() {

        $option = $this->getCurrentOptionValue();

        $this->loadPartials($this->getFullActionName('_').'_l'.$this->_layout_id, false);
        $this->getLayout()->getPartial('content')->setLayoutId('l'.$this->_layout_id);
        $html = array('html' => $this->getLayout()->render());

        if($url = $option->getBackgroundImageUrl()) $html['background_image_url'] = $url;
        $html['use_homepage_background_image'] = (int) $option->getUseHomepageBackgroundImage() && !$option->getHasBackgroundImage();
        $html['title'] = $option->getTabbarName();
        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

    public function loadpostsAction() {

        if($wordpress_url = $this->getRequest()->getPost('wordpress_url')) {

            try {
                $option_value = $this->getCurrentOptionValue();
                $wordpress = $option_value->getObject();

                if(!($wordpress instanceof Wordpress_Model_Wordpress)) {
                    throw new Exception($this->_('An error occurred while loading. Please try again later.'));
                }

                $posts = $wordpress->getRemotePosts($this->getRequest()->getParam('overview'), $wordpress_url, !$this->getRequest()->getParam('overview'));
                $datas = array('posts_html' => $this->getLayout()->addPartial('posts_html', 'core_view_mobile_default', 'wordpress/l'.$this->_layout_id.'/view/posts.phtml')
                    ->setCurrentWordpress($wordpress)
                    ->setOptionValue($option_value)
                    ->setRemotePosts($posts)
                    ->setShowAllPosts($this->getRequest()->getPost('show_all'))
                    ->toHtml()
                );

            }
            catch(Exception $e) {
                $datas = array('error' => 1, 'message' => $e->getMessage());
            }

            $this->_sendHtml($datas);

        }

    }

}