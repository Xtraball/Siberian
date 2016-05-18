<?php

class Folder_MobileController extends Application_Controller_Mobile_Default {

    public function findchildrenAction() {

        if($category_id = $this->getRequest()->getParam('category_id')) {

            try {

                $current_category = new Folder_Model_Category();
                $current_category->find($category_id, 'category_id');
                $object = $this->getCurrentOptionValue()->getObject();

                if(!$current_category->getId() OR !$object->getId() OR $current_category->getRootCategoryId() != $object->getRootCategoryId()) {
                    throw new Exception($this->_('An error occurred during process. Please try again later.'));
                }

                $html = $this->getLayout()->addPartial('category_'.$current_category->getCategoryId(), 'core_view_mobile_default', 'folder/l1/view/category.phtml')
                    ->setCurrentOptionValue($this->getCurrentOptionValue())
                    ->setCurrentCategory($current_category)
                    ->setId($current_category->getId() == $object->getRootCategoryId() ? $object->getValueId() : 'subcategory_'.$current_category->getId())
                    ->toHtml()
                ;

                $html = array(
                    'html' => mb_convert_encoding($html, 'UTF-8', 'UTF-8'),
                    'title' => $current_category->getTitle(),
                );
                if($this->getCurrentOptionValue()->getCode() == 'm_commerce') {
                    $html = array_merge($html, array(
                        'next_button_title' => $this->_('Cart'),
                        'next_button_arrow_is_visible' => 1,
                    ));
                }

                if($url = $this->getCurrentOptionValue()->getBackgroundImageUrl()) $html['background_image_url'] = $url;


            }
            catch(Exception $e) {
                $html = array('error' => 1, 'message' => $e->getMessage());
            }

            $this->_sendHtml($html);

        }

    }

}