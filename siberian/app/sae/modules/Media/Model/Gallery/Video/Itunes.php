<?php

class Media_Model_Gallery_Video_Itunes extends Media_Model_Gallery_Video_Abstract {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Media_Model_Db_Table_Gallery_Video_Itunes';
        return $this;
    }

    public function getVideos($offset) {

        $cache = Zend_Registry::get('cache');

//        if(($this->_videos = $cache->load('MEDIA_VIDEOS_ITUNES_'.sha1($this->getGalleryId().$this->getParam()))) === false ) {

            $this->_videos = array();
            try{
                $flux = Zend_Feed_Reader::import($this->getParam());

                if($flux){
                    foreach($flux as $entry) {
                        $image = $flux->getImage();
                        $podcast = $entry->getExtension('Podcast');
                        $extension = "";
                        if($entry->getEnclosure()->url) {
                            $extension = explode(".", $entry->getEnclosure()->url);
                            $extension = $extension[count($extension)-1];
                        }

                        $video = new Core_Model_Default(array(
                            'video_id'     => $entry->getEnclosure()->url,
                            'title'        => $entry->getTitle(),
                            'description'  => $entry->getContent(),
                            'link'         => $entry->getEnclosure()->url,
                            'extension'    => $extension,
                            'image'        => $podcast->getImage()?$podcast->getImage():$image['uri']
                        ));

                        $this->_videos[] = $video;
                    }
                }
                $cache->save($this->_videos, 'MEDIA_VIDEOS_ITUNES_'.sha1($this->getGalleryId().$this->getParam()));
            }
            catch(Exception $e){

            }
//        }

        return array_slice($this->_videos, $offset-1, self::DISPLAYED_PER_PAGE);
    }

    public function getFields() {
        return $this->getTable()->getFields();
    }


}

