<?php

require_once('lib' . DIRECTORY_SEPARATOR . 'Flickr' . DIRECTORY_SEPARATOR . 'phpflickr' . DIRECTORY_SEPARATOR . 'phpFlickr.php');

class Media_Model_Gallery_Image_Flickr extends Media_Model_Gallery_Image_Abstract {
    protected $endpoints = array(
        'people' => 'people_getPhotos',
        'gallery' => 'galleries_getPhotos',
    );
    protected $cursor = null;
    protected $show_load_more = false;
    const DISPLAYED_PER_PAGE = 20;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Media_Model_Db_Table_Gallery_Image_Flickr';
        return $this;
    }

    /**
     * Returns a collection of images confirming to the Media_Model_Gallery_Image_Abstract contract
     *
     * @param $offset
     * @param int $limit
     * @return array
     */
    public function getImages($offset, $limit = self::DISPLAYED_PER_PAGE) {
        $client = $this->getFlickrClient();
        $params = array(
            'per_page' => self::DISPLAYED_PER_PAGE,
            'extras' => 'owner_name,description,url_n,url_l'
        );
        if ($offset) {
            $params['page'] = $offset;
        }
        // If the identifier is a user's identifier use people_getPhotos, else use galleries_getPhotos
        $getter = $this->endpoints[$this->getType()];
        // Get the Photos
        $data = $client->$getter($this->getIdentifier(), $params);

        $collection = array();
        // If current page is less than total pages then increment the page
        if ($data['photos']['page'] < $data['photos']['pages']) {
            $this->cursor = $data['photos']['page'] + 1;
            $this->show_load_more = true;
        }
        foreach ($data['photos']['photo'] as $photo) {
            $collection[] = new Core_Model_Default(array(
                'offset' => $this->cursor,
                'description' => $photo["description"]["content"],
                'title' => $photo["title"],
                'author' => $photo["ownername"],
                'thumbnail' => "https://farm".$photo["farm"].".staticflickr.com/".$photo["server"]."/".$photo["id"]."_".$photo["secret"]."_t.jpg",
                'image' => "https://farm".$photo["farm"].".staticflickr.com/".$photo["server"]."/".$photo["id"]."_".$photo["secret"]."_z.jpg"
            ));
        }
        return $collection;
    }

    public function showLoadMore() {
        return $this->show_load_more;
    }

    /**
     * Returns the Flickr API Client
     *
     * @return phpFlickr
     */
    public function getFlickrClient() {
        if (!defined('FLICKR_KEY')) {
            $application = $this->getApplication();
            define('FLICKR_KEY', $application->getFlickrKey());
            define('FLICKR_SECRET', $application->getFlickrSecret());
        }
        return new phpFlickr(FLICKR_KEY, FLICKR_SECRET);
    }

    /**
     * Given an identifier, specifies if it's a user a gallery identifier
     *
     * @param $identifier
     * @return string 'people'|'gallery'
     */
    public function guessType($identifier) {
        if ($this->getFlickrClient()->people_findByUsername($identifier)) {
            return "people";
        } else {
            return "gallery";
        }
    }

    /**
     * Refreshes the data if the data are not up to date
     *
     * @return mixed
     */
    protected function refresh() {
        $instance = new Media_Model_Gallery_Image_Flickr();
        $instance->find(array('gallery_id' => $this->getGalleryId()));
        $this->setData($instance->getData());
    }

    public function getIdentifier() {
        if (!$this->_data['identifier']) {
            $this->refresh();
        }
        return $this->_data['identifier'];
    }

    public function getType() {
        if (!$this->_data['type']) {
            $this->refresh();
        }
        return $this->_data['type'];
    }

}
 