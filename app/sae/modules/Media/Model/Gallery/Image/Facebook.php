<?php

class Media_Model_Gallery_Image_Facebook extends Media_Model_Gallery_Image_Abstract {
    protected $page_id = null;
    protected $gallery = null;
    // A pointer to the next page of the images collection
    protected $cursor = null;
    const PREFERED_WIDTH = 480;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Media_Model_Db_Table_Gallery_Image_Facebook';
        return $this;
    }

    public function getPageId() {
        if (!$this->page_id) {
            $gallery = new Media_Model_Gallery_Image();
            $gallery->find($this->getGalleryId());
            $this->gellery = $gallery;
            $this->page_id = $gallery->getName();
        }
        return $this->page_id;
    }

    /**
     * Returns a collection of images confirming to the Media_Model_Gallery_Image_Abstract contract
     *
     * @param $offset
     * @return array
     */
    public function getImages($offset) {
        $album_id = $this->find(array('gallery_id' => $this->getGalleryId()))->getAlbumId();
        $facebook = new Social_Model_Facebook();
        $images = $facebook->getPhotos($album_id, $offset ? $offset : null);
        $collection = array();
        // If the photos have a next page then set the after property
        if ($images["paging"]["next"]) {
            $this->cursor = $images["paging"]["cursors"]["after"];
        }
        // Select the images with the width closest to the PREFERED_WIDTH
        foreach ($images["data"] as $multi_image) {
            usort($multi_image['images'], array('Media_Model_Gallery_Image_Facebook', 'closest'));
            $image = $multi_image['images'][0];
            $collection[] = new Core_Model_Default(array(
                'offset' => $this->cursor,
                'description' => $multi_image['name'],
                'title' => null,
                'author' => null,
                'thumbnail' => null,
                'image' => $image["source"]
            ));
        }
        return $collection;
    }

    public static function closest($a, $b) {
        $d1 = abs($a['width'] - self::PREFERED_WIDTH);
        $d2 = abs($b['width'] - self::PREFERED_WIDTH);
        return $d1 - $d2;
    }

    /**
     * Returns the pointer to the next page of photos if it exists
     *
     * @return null
     */
    public function getCursor() {
        return $this->cursor;
    }

    public function getAlbums() {
        $facebook = new Social_Model_Facebook();
        // Verify the page exists
        $facebook->getPage($this->getPageId());
        // Return the albums
        return $facebook->getAlbums($this->getPageId());
    }
}

