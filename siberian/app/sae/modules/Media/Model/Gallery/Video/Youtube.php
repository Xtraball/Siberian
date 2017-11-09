<?php

class Media_Model_Gallery_Video_Youtube extends Media_Model_Gallery_Video_Abstract {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Media_Model_Db_Table_Gallery_Video_Youtube';
        return $this;
    }

    protected $_flux = array("channel", "search", "user");

    public function getAllTypes() {
        return $this->_flux;
    }

    public function getVideos($offset) {

        if(!$this->_videos) {

            $this->_videos = array();

            //Youtube patch
            //cyril: I see that WTF code and I check the video. It's a video that say "your device is not compatible".
            //So, I guess an old youtube implementation is deprecated and somewhere in the code, a fix is hidden.
            $video = array(
                "video_id" => "UKY3scPIMd8",
                "type" => "youtube",
                "param" => $this->getParam(),
                "type" => $this->getType()
            );
            $this->_videos[] = $video;

            //--END Youtube patch
//            try {
//                $this->_setYoutubeUrl($offset);
//                $feed = Zend_Feed_Reader::import($this->getLink());
//            }
//            catch(Exception $e) {
//                $feed = array();
//            }
//
//
//            foreach ($feed as $entry) {
//                $params = Zend_Uri::factory($entry->getLink())->getQueryAsArray();
//                $image = null;
//                $link = null;
//                if(!empty($params['v'])) {
//                    if($params["v"] == "UKY3scPIMd8") continue;
//                    $image = "http://img.youtube.com/vi/{$params['v']}/0.jpg";
//                    $link = "http://www.youtube.com/embed/{$params['v']}?autoplay=1";
//                }
//                else {
//                    $link = $entry->getLink();
//                }
//
//                $video = new Core_Model_Default(array(
//                    'video_id'     => $params['v'],
//                    'title'        => $entry->getTitle(),
//                    'description'  => $entry->getContent(),
//                    'link'         => $link,
//                    'image'        => $image
//                ));
//
//                $this->_videos[] = $video;
//            }

        }

        return $this->_videos;
    }

    public function getFields() {
        return $this->getTable()->getFields();
    }

}

