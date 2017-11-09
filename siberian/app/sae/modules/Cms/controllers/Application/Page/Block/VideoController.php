<?php

class Cms_Application_Page_Block_VideoController extends Application_Controller_Default {

    /**
     * Récupère les vidéos youtube ou podcast associées à une recherche
     */
    public function searchAction() {
        if($datas = $this->getRequest()->getParams()) {

            $data = array();

            try {
                $datas = current($datas['block']);
                $video = new Cms_Model_Application_Page_Block_Video();
                $video->setTypeId($datas['type_id']);
                $videos = $video->getList($datas['search']);

                $data['layout'] = $this->getLayout()
                    ->addPartial('row', 'admin_view_default', 'cms/application/page/edit/block/video/search.phtml')
                    ->setCurrentOptionValue($this->getCurrentOptionValue())
                    ->setTypeId($datas['type_id'])
                    ->setVideos($videos)
                    ->toHtml()
                ;

            } catch (Exception $e) {
                $data = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->_sendJson($data);
        }
    }

    public function searchv2Action() {
        if($datas = $this->getRequest()->getParams()) {
            try {
                $video = new Cms_Model_Application_Page_Block_Video();
                $video->setTypeId($datas["type"]);
                $videos = $video->getList($datas["search"]);

                $vids = array();
                foreach($videos as $video) {
                    $vids[] = $video->getData();
                }

                $data = array(
                    "success" => true,
                    "videos" => $vids
                );

            } catch (Exception $e) {
                $data = array(
                    "error" => true,
                    "message" => $e->getMessage(),
                    "message_button" => 1,
                    "message_loader" => 1
                );
            }

            $this->_sendJson($data);
        }
    }

}
