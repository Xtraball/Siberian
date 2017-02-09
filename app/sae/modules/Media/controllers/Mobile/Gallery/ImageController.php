<?php

class Media_Mobile_Gallery_ImageController extends Application_Controller_Mobile_Default {

    public function listAction() {

        if ($datas = $this->getRequest()->getPost()) {

            try {

                if (empty($datas['gallery_id']))
                    throw new Exception($this->_('An error occurred while loading pictures. Please try later.'));

                $image = new Media_Model_Gallery_Image();
                $image->find($datas['gallery_id']);
                if (!$image->getId() OR $image->getValueId() != $this->getCurrentOptionValue()->getId())
                    throw new Exception($this->_('An error occurred while loading pictures. Please try later.'));

                $this->loadPartials($this->getFullActionName('_') . '_l' . $this->_layout_id, false);
                $this->getLayout()->getPartial('content')->setCurrentImage($image);

                if($this->getRequest()->getParam("all") == 1) {
                    $imgs = $image->getAllImages();
                } else {
                    $imgs = $image->getImages();
                }

                $images = array();
                foreach($imgs as $link) {
                    $images[] = $link->getData();
                }
                $html = array('html' => $this->getLayout()->render(), 'title' => $this->getCurrentOptionValue()->getTabbarName(), 'id' => $image->getId(), 'images' => $images);

            }
            catch(Exception $e) {
                $html = array('error' => 1, 'message' => $e->getMessage());
            }

            $this->_sendHtml($html);

        }

    }

    public function loadmoreAction() {

        if ($datas = $this->getRequest()->getPost()) {

            try {

                if (empty($datas['gallery_id']))
                    throw new Exception($this->_('An error occurred while loading pictures. Please try later.'));
                $offset = !empty($datas['offset']) ? $datas['offset'] : 0;

                $image = new Media_Model_Gallery_Image();
                $image->find($datas['gallery_id']);
                if (!$image->getId() OR $image->getValueId() != $this->getCurrentOptionValue()->getId())
                    throw new Exception($this->_('An error occurred while loading pictures. Please try later.'));

                $images = $image->setOffset($offset)->getImages();
                $html = array();
                $image_datas = array();
//                $offset--;

                foreach ($images as $key => $link) {
                    $key+=$offset;
                    $html[] = $this->getLayout()->addPartial('row', 'core_view_default', 'media/gallery/image/l1/view/list/li.phtml')->setCurrentImage($image)->setKey($key)->setLink($link)->toHtml();
                    $image_datas[] = $link->getData();
                }

                $html = array('html' => implode('', $html), 'images' => $image_datas, 'id' => $image->getId());
            } catch (Exception $e) {
                $html = array('error' => 1, 'message' => $e->getMessage());
            }

            $this->_sendHtml($html);
        }
    }

}
