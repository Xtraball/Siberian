<?php

class Media_Mobile_Gallery_VideoController extends Application_Controller_Mobile_Default {

    public function listAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {

                if(empty($datas['video_id'])) throw new Exception($this->_('An error occurred while loading videos. Please try later.'));

                $video = new Media_Model_Gallery_Video();
                $video->find($datas['video_id']);
                if(!$video->getId() OR $video->getValueId() != $this->getCurrentOptionValue()->getId()) throw new Exception($this->_('An error occurred while loading videos. Please try later.'));

                $this->loadPartials($this->getFullActionName('_').'_l'.$this->_layout_id, false);
                $this->getLayout()->getPartial('content')->setVideo($video)->setCurrentOption($this->getCurrentOptionValue());
                $html = array('html' => $this->getLayout()->render());

            }
            catch(Exception $e) {
                $html = array('error' => 1, 'message' => $e->getMessage());
            }

            $this->_sendHtml($html);

        }

    }

    public function loadmoreAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {

                if(empty($datas['video_id'])) throw new Exception($this->_('An error occurred while loading videos. Please try later.'));
                $offset = !empty($datas['offset']) ? $datas['offset'] : 1;

                $video = new Media_Model_Gallery_Video();
                $video->find($datas['video_id']);
                if(!$video->getId() OR $video->getValueId() != $this->getCurrentOptionValue()->getId()) throw new Exception($this->_('An error occurred while loading videos. Please try later.'));
                $videos = $video->setOffset($offset)->getVideos();
                $html = array();

                foreach($videos as $key => $link) {
                    $key+=$offset;
                    $html[] = $this->getLayout()->addPartial('row', 'core_view_default', 'media/gallery/video/l1/view/list/li.phtml')
                        ->setCurrentOption($this->getCurrentOptionValue())
                        ->setCurrentVideo($video)
                        ->setKey($key)
                        ->setLink($link)
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

}