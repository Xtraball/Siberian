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

    /**
     * @param array $data
     * @return $this
     */
    public function populate($data = array()) {

        $this
            ->setSearch($data["podcast_search"])
            ->setLink($data["podcast"])
        ;

        return $this;
    }

    public function getImageUrl() {
        return $this->getImage();
    }

    /**
     * Récupère les podcasts
     *
     * @param $flux
     * @param null $id
     * @return array
     */
    public function getList($flux, $id = null) {

        Zend_Feed_Reader::registerExtension('Media');

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
                if (strpos($enclosure->type, "video") !== false) {

                    $image = $feed->getImage();
                    $podcastExt = $entry->getExtension("Podcast");

                    $media = $entry->getExtension("Media");
                    $thumbnails = $media->getThumbnails();

                    # Best image
                    $image = false;
                    $min_width = 5000;
                    if(sizeof($thumbnails) > 0) {
                        foreach($thumbnails as $thumbnail) {
                            if($thumbnail["width"] < $min_width) {
                                $min_width = $thumbnail["width"];
                                $thumb = $thumbnail["url"];
                            }
                        }
                        if(is_image($thumb, true) !== false) {
                            $image = $thumb;
                        }
                    }

                    if(!$image) {
                        if(is_image($podcastExt->getImage(), true) !== false) {
                            $image = $podcastExt->getImage();
                        } elseif(is_image($image["uri"], true) !== false) {
                            $image = $image["uri"];
                        } else {
                            # Placeholder
                            $image = "/images/application/placeholder/placeholder-video.png";
                        }
                    }

                    $podcast = new Core_Model_Default(array(
                        "id"            => $entry->getId(),
                        "title"         => $entry->getTitle(),
                        "description"   => $entry->getContent(),
                        "link"          => $entry->getEnclosure()->url,
                        "image"         => $image
                    ));

                    /** Return a single podcast */
                    if(!is_null($id) && ($id == $entry->getId())) {
                        return $podcast;
                    }

                    $this->_podcasts[] = $podcast;
                }

            }
        }

        return $this->_podcasts;
    }

}

