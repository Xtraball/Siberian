<?php

class Cms_Application_Page_Block_VideoController extends Application_Controller_Default
{

    /**
     * Récupère les vidéos youtube ou podcast associées à une recherche
     */
    public function searchAction()
    {
        if ($datas = $this->getRequest()->getParams()) {

            $application = $this->getApplication();
            $youtubeKey = $application->getYoutubeKey();

            $data = [];

            try {
                $datas = current($datas['block']);
                $video = new Cms_Model_Application_Page_Block_Video();
                $video->setTypeId($datas['type_id']);
                $videos = $video->getList($datas['search'], null, $youtubeKey);

                $data['layout'] = $this->getLayout()
                    ->addPartial('row', 'admin_view_default', 'cms/application/page/edit/block/video/search.phtml')
                    ->setCurrentOptionValue($this->getCurrentOptionValue())
                    ->setTypeId($datas['type_id'])
                    ->setVideos($videos)
                    ->toHtml();

            } catch (Exception $e) {
                $data = [
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                ];
            }

            $this->_sendJson($data);
        }
    }

    public function searchv2Action()
    {
        try {
            $request = $this->getRequest();
            $datas = $request->getParams();
            $application = $this->getApplication();
            $youtubeKey = $application->getYoutubeKey();
            $video = (new Cms_Model_Application_Page_Block_Video())
                ->setTypeId($datas['type']);
            $videos = $video->getList($datas['search'], 'video_id', $youtubeKey);

            $vids = [];
            foreach ($videos as $video) {
                $vids[] = $video->getData();
            }

            $data = [
                "success" => true,
                "videos" => $vids
            ];

        } catch (\Exception $e) {
            $data = [
                "error" => true,
                "message" => $e->getMessage(),
                "message_button" => 1,
                "message_loader" => 1
            ];
        }

        $this->_sendJson($data);
    }

}
