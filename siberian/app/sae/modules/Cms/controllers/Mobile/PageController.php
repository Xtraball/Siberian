<?php

class Cms_Mobile_PageController extends Application_Controller_Mobile_Default
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

}