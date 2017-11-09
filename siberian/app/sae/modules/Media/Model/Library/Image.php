<?php

class Media_Model_Library_Image extends Core_Model_Default {

    const PATH = '/images/library';
    const APPLICATION_PATH = '/images/application/%d/icons';

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Media_Model_Db_Table_Library_Image';
        return $this;
    }

    public static function getImagePathTo($path = '', $app_id = null) {

        if(!empty($path) AND substr($path, 0, 1) != '/') {
            $path = '/'.$path;
        }

        if(!is_null($app_id)) {
            $path = sprintf(self::APPLICATION_PATH.$path, $app_id);
        } else if(strpos($path, "/app") === 0) {
            # Do nothing for /app/* from modules
        } else {
            $path = self::PATH.$path;
        }

        return Core_Model_Directory::getPathTo($path);
    }

    public static function getBaseImagePathTo($path = '', $app_id = null) {

        if(!empty($path) AND substr($path,0,1) != '/') $path = '/'.$path;

        if(!is_null($app_id)) {
            $path = sprintf(self::APPLICATION_PATH.$path, $app_id);
        } else if(strpos($path, "/app") === 0) {
            # Do nothing for /app/* from modules
        } else {
            $path = self::PATH.$path;
        }

        return Core_Model_Directory::getBasePathTo($path);

    }

    /**
     * The params are prefixed with __ to avoid conflict with internal params.
     *
     * @deprecated will be deprecated in 4.2.x `
     *
     * @param string $__url
     * @param array $__params
     * @param null $__locale
     * @return string
     */
    public function getUrl($__url = '', array $__params = array(), $__locale = null) {
        $url = '';
        if($this->getLink()) {
            $url = self::getImagePathTo($this->getLink(), $this->getAppId());
            $base_url = self::getBaseImagePathTo($this->getLink(), $this->getAppId());
            if(!file_exists($base_url) ) {
                $url = '';
            }
        }

        if(empty($url)) {
            $url = $this->getNoImage();
        }
        return $url;

    }

    public function getSecondaryUrl() {
        $url = '';
        if($this->getSecondaryLink()) {
            $url = self::getImagePathTo($this->getSecondaryLink());
            if(!file_exists(self::getBaseImagePathTo($this->getSecondaryLink()))) $url = '';
        }

        if(empty($url)) {
            $url = $this->getNoImage();
        }

        return $url;

    }

    public function getThumbnailUrl() {
        $url = '';
        if($this->getThumbnail()) {
            $url = self::getImagePathTo($this->getThumbnail());
            if(!file_exists(self::getBaseImagePathTo($this->getThumbnail()))) $url = '';
        }

        if(empty($url)) {
            $url = $this->getUrl();
        }

        return $url;
    }

    public function updatePositions($positions) {
        $this->getTable()->updatePositions($positions);

        return $this;
    }
}
