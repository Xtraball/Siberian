<?php

/**
 * Class Media_Model_Gallery_Image_Facebook
 *
 * @method $this setImageId(integer $imageId)
 * @method integer getGalleryId()
 */
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
     * Fetch current & next album urls, first if no url given
     *
     * @param null $albumUrl
     * @return array
     */
    public function getAlbumUrls($albumUrl = null) {
        if ($albumUrl === null) {
            $albumId = $this->find([
                'gallery_id' => $this->getGalleryId()
            ])->getAlbumId();

            $albumUrl = (new Social_Model_Facebook())
                ->getAlbumUrl($albumId);
        }

        $albumUrls = (new Social_Model_Facebook())
            ->getAlbumUrls($albumUrl);

        return $albumUrls;
    }

    /**
     * Facebook something!
     *
     * @param $albumUrlPage
     * @return array
     */
    public function getImagesForUrl($albumUrlPage) {
        $response = file_get_contents($albumUrlPage);
        $response = Siberian_Json::decode($response);

        $collection = [];

        // Select images with the biggest resolution!
        foreach ($response['data'] as $multiImage) {

            $lastImageWidth = 0;
            $thumbnailSource = null;
            $imageSource = null;

            foreach($multiImage['images'] as $image) {
                $imageWidth = $image['width'];
                if ($imageWidth < 500) {
                    $thumbnailSource = $image['source'];
                }

                // Keep the biggest image!
                if ($imageWidth > $lastImageWidth) {
                    $lastImageWidth = $imageWidth;
                    $imageSource = $image['source'];
                }
            }

            $collection[] = new Core_Model_Default([
                'description' => (isset($multiImage['name'])) ? $multiImage['name'] : null,
                'title' => null,
                'author' => null,
                'thumbnail' => $thumbnailSource,
                'image' => $imageSource,
            ]);
        }

        return $collection;
    }

    /**
     * Returns a collection of images confirming to the Media_Model_Gallery_Image_Abstract contract
     *
     * @param $offset
     * @param int $limit
     * @return array
     */
    public function getImages($offset, $limit = self::DISPLAYED_PER_PAGE) {
        $album_id = $this->find([
            'gallery_id' => $this->getGalleryId()
        ])->getAlbumId();

        $images = (new Social_Model_Facebook())
            ->getPhotos($album_id, $offset ? $offset : null);

        $collection = [];

        // Select the images with the width closest to the PREFERED_WIDTH
        foreach ($images['data'] as $multi_image) {
            usort($multi_image['images'], ['Media_Model_Gallery_Image_Facebook', 'closest']);
            $image = $multi_image['images'][0];
            $collection[] = new Core_Model_Default([
                'offset' => $this->cursor,
                'description' => $multi_image['name'],
                'title' => null,
                'author' => null,
                'thumbnail' => null,
                'image' => $image['source'],
            ]);
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

