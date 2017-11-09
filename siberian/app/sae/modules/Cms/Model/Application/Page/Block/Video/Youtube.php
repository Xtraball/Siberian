<?php

class Cms_Model_Application_Page_Block_Video_Youtube  extends Core_Model_Default {

    private $_videos;
    private $_link;
    protected $_flux = array(
        'search' => 'https://www.googleapis.com/youtube/v3/search/?q=%s1&type=search&part=snippet&key=%s2&maxResults=%d2',
        'video_id' => 'https://www.googleapis.com/youtube/v3/videos?id=%s1&key=%s2&part=snippet'
    );

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Cms_Model_Db_Table_Application_Page_Block_Video_Youtube';
        return $this;
    }

    public function isValid() {
        if($this->getYoutube()) {
            return true;
        }

        return false;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function populate($data = array()) {

        $this
            ->setSearch($data["youtube_search"])
            ->setYoutube($data["youtube"])
        ;

        return $this;
    }

    public function getImageUrl() {
        return "https://img.youtube.com/vi/{$this->getYoutube()}/0.jpg";
    }

    /**
     * Récupère les vidéos youtube
     *
     * @param string $search
     * @return array
     */
    public function getList($search, $type = "video_id") {

        if(is_null($type)) {
            $type = "video_id";
        }

        if(!$this->_videos) {

            $this->_videos = array();

            try {
                $video_id = $search;
                if(Zend_Uri::check($search)) {
                    $params = Zend_Uri::factory($search)->getQueryAsArray();
                    if(!empty($params['v'])) {
                        $video_id = $params['v'];
                    }
                }

                $this->_setYoutubeUrl($search, $type);

                $datas = file_get_contents($this->getLink());

                try { 
                    $datas = Zend_Json::decode($datas); 
                } catch(Exception $e) { 
                    $datas = array(); 
                }

                if($datas AND !empty($datas['pageInfo']['totalResults'])) {
                    
                    $feed = array();

                    foreach($datas['items'] as $item) {

                        $video_id = null;
                        if(!empty($item["id"]["videoId"])) $video_id = $item["id"]["videoId"];
                        elseif(!empty($item["id"]) AND !is_array($item["id"])) $video_id = $item["id"];

                        if(is_null($video_id)) continue;
                        
                        $feed[] = new Core_Model_Default(array(
                            'title' => !empty($item['snippet']['title']) ? $item['snippet']['title'] : null,
                            'content' => !empty($item['snippet']['description']) ? $item['snippet']['description'] : null,
                            'link'  => "https://www.youtube.com/watch?v={$video_id}",
                            'image' => "https://img.youtube.com/vi/{$video_id}/0.jpg"
                        ));
                    }

                }
                else if($type == "video_id") {
                    return $this->getList($search, "search");
                }

            } catch(Exception $e) {
                $feed = array();
            }

            foreach ($feed as $entry) {
                $params = Zend_Uri::factory($entry->getLink())->getQueryAsArray();
                if(empty($params['v'])) continue;

                $video = new Core_Model_Default(array(
                    'id'           => $params['v'],
                    'title'        => $entry->getTitle(),
                    'description'  => $entry->getContent(),
                    'link'         => "https://www.youtube.com/embed/{$params['v']}",
                    'image'        => "https://img.youtube.com/vi/{$params['v']}/0.jpg"
                ));

                $this->_videos[] = $video;
            }

        }

        return $this->_videos;
    }

    public function getVideo($id) {

    }

    /**
     * Construit le lien
     *
     * @param string $search
     * @return \Cms_Model_Application_Page_Block_Youtube
     */
    protected function _setYoutubeUrl($search, $type) {

        $api_key = Api_Model_Key::findKeysFor('youtube')->getApiKey();
        $flux = $this->_flux[$type];
        $search = str_replace(' ', '+', $search);
        $url = str_replace('%s1', $search, $flux);
        $url = str_replace('%s2', $api_key, $url);
        $url = str_replace('%d1', '1', $url);
        $url = str_replace('%d2', '24', $url);
        $this->setLink($url);
        return $this;
    }

    protected function setLink($url) {
        $this->_link = $url;
    }

    protected function getLink() {
        return $this->_link;
    }

}

