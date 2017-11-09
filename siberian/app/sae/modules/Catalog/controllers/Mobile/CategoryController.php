<?php

class Catalog_Mobile_CategoryController extends Application_Controller_Mobile_Default
{
    public function viewAction() {

        $option = $this->getCurrentOptionValue();
        $this->loadPartials($this->getFullActionName('_').'_l'.$this->_layout_id, false);

        $category = new Catalog_Model_Category();
        $categories = $category->findByValueId($option->getId(), null, true, true);
        $this->getLayout()->getPartial('content')->setCategories($categories);

        $html = array('html' => $this->getLayout()->render(), 'title' => $this->getCurrentOptionValue()->getTabbarName());
        if($url = $option->getBackgroundImageUrl()) $html['background_image_url'] = $url;
        $html['use_homepage_background_image'] = (int) $option->getUseHomepageBackgroundImage() && !$option->getHasBackgroundImage();
        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

}