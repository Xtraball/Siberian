<?php

class Media_Model_Gallery_Image_Picasa extends Media_Model_Gallery_Image_Abstract {

    protected $_albums = array();

    protected $_flux = array(
        'album' => 'https://picasaweb.google.com/data/feed/api/user/%s/%ai?start-index=%d1&max-results=%d2',
        'search' => 'https://picasaweb.google.com/data/feed/api/all?q=%s&start-index=%d1&max-results=%d2'
    );

    protected $_labels = array(
        'album' => 'Album',
        'search' => 'Recherche'
    );

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Media_Model_Db_Table_Gallery_Image_Picasa';
        return $this;
    }

    public function getAllTypes() {
        $types = array();
        foreach($this->_flux as $k => $flux) {
            $types[$k] = new Core_Model_Default(array(
                'code' => $k,
                'url' => $flux,
                'label' => $this->_labels[$k]
            ));
        }
        return $types;
    }

    public function findAlbums() {

        if(!$this->_albums) {

            $this->_albums = array();

            Zend_Feed_Reader::registerExtension('Picasa');

            try {
                if (empty($offset)) {
                    $offset = 1;
                }
                $this->setType('album')->unsAlbumId();
                $this->_setPicasaUrl($offset);
                $feed = Zend_Feed_Reader::import($this->getLink());
            }
            catch(Exception $e) {
                $feed = array();
            }

            foreach ($feed as $entry) {
                $picasa = $entry->getExtension('Picasa');

                $this->_albums[] = array(
                    'id' => $picasa->getAlbumId(),
                    'title' => $picasa->getTitle(),
                    'author' => $picasa->getAuthor(),
                    'image' => $picasa->getImage(),
                );

            }

        }

        return $this->_albums;
    }

    public function getImages($offset, $limit = self::DISPLAYED_PER_PAGE) {

        if(!$this->_images) {

            $this->_images = array();

            try {
                if(empty($offset)) $offset = 0;
                else $offset += 1;

//                $orig_offset = $offset;
                $this->_setPicasaUrl($offset);
                $feed = Zend_Feed_Reader::import($this->getLink());
            }
            catch(Exception $e) {
                $feed = array();
            }

            $images = array();
            foreach ($feed as $key => $entry) {
                /**
                * @bug Solution provisoire en attendant que Google règle le problème du start-index
                */
//                if($key < $offset) continue;
                $author = $entry->getAuthor();
                $image = '';
                foreach($entry->getElement()->getElementsByTagName('content') as $content) {
                    $src = (string) $content->getAttribute('src');
                    if(!empty($src)) $image = $src;
                }

                $this->_images[] = new Core_Model_Default(array(
                    'offset'  => ++$offset,
                    'title'  => $entry->getTitle(),
                    'description' => $entry->getDescription(),
                    'author' => $author['name'],
                    'image'  => $image
                ));

                /**
                * @bug Solution provisoire en attendant que Google règle le problème du start-index
                */
//                if($offset >= $orig_offset + self::DISPLAYED_PER_PAGE) break;
            }

        }

        return $this->_images;
    }

    protected function _setPicasaUrl($offset) {
        if($offset == 0) $offset = 1;
        $url = str_replace('%s', $this->getParam(), $this->_flux[$this->getType()]);
        $url = str_replace('%ai', $this->getAlbumId() ? 'albumid/'.$this->getAlbumId().'/' : '', $url);
        $url = str_replace('%d1', $offset, $url);
        $url = str_replace('%d2', self::DISPLAYED_PER_PAGE, $url);
//        $url = str_replace('%d2', 100, $url);
        $this->setLink($url);
        return $this;
    }

}

