<?php

class Catalog_Mobile_ProductController extends Application_Controller_Mobile_Default {

    public function viewAction() {

        try {
            $product = new Catalog_Model_Product();
            $product->find($this->getRequest()->getParam('product_id'));
            if(!$product->getId() OR $product->getValueId() != $this->getCurrentOptionValue()->getId()) {
                throw new Exception($this->_('An error occurred while loading your product.'));
            }

            $option = $this->getCurrentOptionValue();
            $this->loadPartials($this->getFullActionName('_').'_l'.$this->_layout_id, false);
            $this->getLayout()->getPartial('content')->setCurrentProduct($product);

            $html = array('html' => $this->getLayout()->render(), 'title' => $option->getTabbarName());
            if($url = $option->getBackgroundImageUrl()) $html['background_image_url'] = $url;
            $html['use_homepage_background_image'] = (int) $option->getUseHomepageBackgroundImage() && !$option->getHasBackgroundImage();

            $html = array_merge($html, array(
                'next_button_title' => $this->_('Cart'),
                'next_button_arrow_is_visible' => 1,
            ));

        } catch (Exception $e) {
            $html = array('message' => $e->getMessage());
        }

        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

}