<?php

/**
 * Class Cms_Model_Application_Page_Block_Video_Youtube
 */
class Cms_Model_Application_Page_Block_Video_Youtube extends Core_Model_Default
{

    /**
     * @var
     */
    private $_videos;
    /**
     * @var
     */
    private $_link;
    /**
     * @var array
     */
    protected $_flux = [
        'search' => 'https://www.googleapis.com/youtube/v3/search/?q=%s1&type=search&part=snippet&key=%s2&maxResults=%d2',
        'video_id' => 'https://www.googleapis.com/youtube/v3/videos?id=%s1&key=%s2&part=snippet'
    ];

    /**
     * @var string
     */
    protected $_db_table = Cms_Model_Db_Table_Application_Page_Block_Video_Youtube::class;

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        if ($this->getYoutube()) {
            return true;
        }

        return false;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function populate($data = []): self
    {

        $this
            ->setSearch($data['youtube_search'])
            ->setYoutube($data['youtube']);

        return $this;
    }

    /**
     * @return string
     */
    public function getImageUrl(): string
    {
        return "https://img.youtube.com/vi/{$this->getYoutube()}/0.jpg";
    }

    /**
     * @param $search
     * @param string $type
     * @param string $youtubeKey
     * @return array
     * @throws Zend_Uri_Exception
     */
    public function getList($search, $type = 'video_id', $youtubeKey = ''): array
    {
        if (is_null($type)) {
            $type = 'video_id';
        }

        if (!$this->_videos) {
            $this->_videos = [];
            $feed = [];
            try {
                $video_id = $search;
                if (Zend_Uri::check($search)) {
                    $params = Zend_Uri::factory($search)->getQueryAsArray();
                    if (!empty($params['v'])) {
                        $video_id = $params['v'];
                    }
                }

                $this->_setYoutubeUrl($search, $type, $youtubeKey);
                $datas = file_get_contents($this->getLink());

                try {
                    $datas = Zend_Json::decode($datas);
                } catch (\Exception $e) {
                    $datas = [];
                }

                if ($datas && !empty($datas['pageInfo']['totalResults'])) {

                    $feed = [];

                    foreach ($datas['items'] as $item) {
                        $video_id = null;
                        if (!empty($item['id']['videoId'])) {
                            $video_id = $item['id']['videoId'];
                        } else if (!empty($item['id']) && !is_array($item['id'])) {
                            $video_id = $item['id'];
                        }

                        if (is_null($video_id)) {
                            continue;
                        }

                        $feed[] = new Core_Model_Default([
                            'title' => !empty($item['snippet']['title']) ? $item['snippet']['title'] : null,
                            'content' => !empty($item['snippet']['description']) ? $item['snippet']['description'] : null,
                            'link' => "https://www.youtube.com/watch?v={$video_id}",
                            'image' => "https://img.youtube.com/vi/{$video_id}/0.jpg"
                        ]);
                    }

                } else if ($type === 'video_id') {
                    return $this->getList($search, 'search', $youtubeKey);
                }

            } catch (Exception $e) {
                $feed = [];
            }

            foreach ($feed as $entry) {
                $params = Zend_Uri::factory($entry->getLink())->getQueryAsArray();
                if (empty($params['v'])) {
                    continue;
                }

                $video = new Core_Model_Default([
                    'id' => $params['v'],
                    'title' => $entry->getTitle(),
                    'description' => $entry->getContent(),
                    'link' => "https://www.youtube.com/embed/{$params['v']}",
                    'image' => "https://img.youtube.com/vi/{$params['v']}/0.jpg"
                ]);

                $this->_videos[] = $video;
            }

        }

        return $this->_videos;
    }

    /**
     * @param $id
     */
    public function getVideo($id)
    {

    }

    /**
     * @param $search
     * @param $type
     * @param string $youtubeKey
     * @return $this
     */
    protected function _setYoutubeUrl($search, $type, $youtubeKey = ''): self
    {
        $flux = $this->_flux[$type];
        $search = str_replace(' ', '+', $search);
        $url = str_replace(
            ['%s1', '%s2', '%d1', '%d2'],
            [$search, $youtubeKey, '1', '24'],
            $flux);
        $this->setLink($url);

        return $this;
    }

    /**
     * @param $url
     */
    protected function setLink($url)
    {
        $this->_link = $url;
    }

    /**
     * @return mixed
     */
    public function getLink()
    {
        return $this->_link;
    }

}

