<?php

/**
 * @see Zend_Feed_Reader
 */
require_once 'Zend/Feed/Reader.php';

/**
 * @see Zend_Feed_Reader_Extension_EntryAbstract
 */
require_once 'Zend/Feed/Reader/Extension/EntryAbstract.php';

class Zend_Feed_Reader_Extension_Picasa_Entry extends Zend_Feed_Reader_Extension_EntryAbstract
{
    /**
     * Get the entry author
     *
     * @return string
     */
    public function getAuthor()
    {
        if (isset($this->_data['author'])) {
            return $this->_data['author'];
        }

        $author = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/gphoto:nickname)');

        if (!$author) {
            $author = null;
        }

        $this->_data['author'] = $author;

        return $this->_data['author'];
    }

    /**
     * Get the entry album_id
     *
     * @return string
     */
    public function getAlbumId() {
        if (isset($this->_data['album_id'])) {
            return $this->_data['album_id'];
        }

        $album_id = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/gphoto:id)');

        if (!$album_id) {
            $album_id = null;
        }

        $this->_data['album_id'] = $album_id;

        return $this->_data['album_id'];
    }

    /**
     * Get the entry image
     *
     * @return string
     */
    public function getImage() {
        if (isset($this->_data['image'])) {
            return $this->_data['image'];
        }

        $image = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/media:group/media:content/@url)');

        if (!$image) {
            $image = null;
        }

        $this->_data['image'] = $image;

        return $this->_data['image'];
    }

    /**
     * Get the entry title
     *
     * @return string
     */
    public function getTitle() {
        if (isset($this->_data['title'])) {
            return $this->_data['title'];
        }

        $title = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/media:group/media:title)');

        if (!$title) {
            $title = null;
        }

        $this->_data['title'] = $title;

        return $this->_data['title'];
    }


    /**
     * Register Picasa namespace
     *
     */
    protected function _registerNamespaces()
    {
        $this->_xpath->registerNamespace('gphoto', 'http://schemas.google.com/photos/2007');
    }
}