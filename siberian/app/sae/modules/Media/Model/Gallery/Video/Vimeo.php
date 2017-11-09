<?php

class Media_Model_Gallery_Video_Vimeo extends Media_Model_Gallery_Video_Abstract {

    const PLAYER_URL = 'https://player.vimeo.com/video/';

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Media_Model_Db_Table_Gallery_Video_Vimeo';
        return $this;
    }

    protected $_flux = array(
        'user'   => 'https://vimeo.com/api/v2/user',
        'group'  => 'https://vimeo.com/api/v2/group',
        'channel'=> 'https://vimeo.com/api/v2/channel',
        'album'  => 'https://vimeo.com/api/v2/album',
    );

    public function getAllTypes() {
        return array_keys($this->_flux);
    }

    public function getVideos($offset) {

        $offset--;
        if(!($offset%20)) $page = ceil(($offset+1)/20);
        else $page = ceil($offset/20);

        if($offset >= 20) $offset -= 20*($page-1);

        $url = $this->_flux[$this->getType()].'/'.$this->getParam().'/'.'videos.php?page='.$page;

        $cache = Zend_Registry::get('cache');


            $this->_videos = array();
            try{

                $curl = curl_init($url);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_TIMEOUT, 30);
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
                $flux = unserialize(curl_exec($curl));
                curl_close($curl);

                if($flux){
                    foreach($flux as $key => $entry) {

//                        if(empty($entry['mobile_url'])) continue;
                        $video = new Core_Model_Default(array(
                            'video_id'     => $entry['id'],
                            'title'        => $entry['title'],
                            'description'  => $entry['description'],
                            'link'         => "https://player.vimeo.com/video/".$entry['id']."?autoplay=1",
                            'image'        => $entry['thumbnail_medium']
                        ));

                        $this->_videos[] = $video;
                    }
                }

                $cache->save($this->_videos, 'MEDIA_VIDEOS_VIMEO_'.sha1($this->getGalleryId().$url));

            } catch(Exception $e) {}

//        }

        return array_slice($this->_videos, $offset, self::DISPLAYED_PER_PAGE);
    }

    public function getFields() {
        return $this->getTable()->getFields();
    }

}

