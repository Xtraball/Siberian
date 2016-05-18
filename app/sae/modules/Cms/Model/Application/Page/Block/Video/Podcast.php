<?php

class Cms_Model_Application_Page_Block_Video_Podcast extends Core_Model_Default {

    protected $_podcasts;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Cms_Model_Db_Table_Application_Page_Block_Video_Podcast';
        return $this;
    }

    public function isValid() {
        return $this->getLink() && $this->getSearch();
    }

    public function getImageUrl() {
        return $this->getImage();
    }

    /**
     * Récupère les podcasts
     *
     * @param string $search
     * @return array
     */
    public function getList($flux) {

        if (!$this->_podcasts) {

            $this->_podcasts = array();

            try {
                if($flux) {
                    $feed = Zend_Feed_Reader::import($flux);
                }
            } catch (Exception $e) {
                $feed = array();
            }

            foreach ($feed as $entry) {
                $enclosure = $entry->getEnclosure();
                if (strpos($enclosure->type, 'video') !== false) {

                    $image = $feed->getImage();
                    $podcastExt = $entry->getExtension('Podcast');

                    $podcast = new Core_Model_Default(array(
                        'id' => $entry->getId(),
                        'title' => $entry->getTitle(),
                        'description' => $entry->getContent(),
                        'link' => $entry->getEnclosure()->url,
			'image' => $podcastExt->getImage()?$podcastExt->getImage():$image['uri']
                    ));

                    $this->_podcasts[] = $podcast;
                }
            }
        }

        return $this->_podcasts;
    }

}

