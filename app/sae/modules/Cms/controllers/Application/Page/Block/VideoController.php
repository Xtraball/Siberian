<?php

class Cms_Application_Page_Block_VideoController extends Application_Controller_Default {

    /**
     * Récupère les vidéos youtube ou podcast associées à une recherche
     */
    public function searchAction() {
        if ($datas = $this->getRequest()->getPost()) {

            $html = '';

            try {
                $datas = current($datas['block']);
                $video = new Cms_Model_Application_Page_Block_Video();
                $video->setTypeId($datas['type_id']);
                $videos = $video->getList($datas['search']);

                $html['layout'] = $this->getLayout()
                    ->addPartial('row', 'admin_view_default', 'cms/application/page/edit/block/video/search.phtml')
                    ->setCurrentOptionValue($this->getCurrentOptionValue())
                    ->setTypeId($datas['type_id'])
                    ->setVideos($videos)
                    ->toHtml()
                ;

            } catch (Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

}
