<?php

class Media_Model_Gallery_Music_Track extends Core_Model_Default {

    protected $_track;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Media_Model_Db_Table_Gallery_Music_Track';
        return $this;
    }

    public function getTrack() {

        if(!$this->_track) {
            $this->_track = new Media_Model_Gallery_Music_Track();
            $this->_track->find($this->getTrackId());
        }

        return $this->_track;
    }

    public function getArtworkUrl() {
        $artwork_url = $this->getData('artwork_url');
        if($artwork_url) {
            if($this->getType() == 'custom') {
                return Application_Model_Application::getImagePath().$artwork_url;
            } else {
                return $artwork_url;
            }
        } else {
            return Media_Model_Library_Image::getImagePathTo('musics/default_album.jpg');
        }
    }

    public function getNextTrackPosition() {
        $lastPosition = $this->getTable()->getLastTrackPosition();
        if(!$lastPosition) $lastPosition = 0;

        return ++$lastPosition;
    }

    public function getFormatedDuration($millis = "") {

        if($this->getType() == "podcast") {
            return $this->getData("formatted_duration");
        }

        if($millis == "") {
            $track = $this->getTrack();
            $millis = $track->getDuration();
        }
        $millis = floor($millis / 1000);
        $seconds = $millis % 60;
        if(strlen($seconds) == 1) {
            $seconds.= '0';
        }
        $millis = floor($millis / 60);
        $minutes = $millis % 60;

        return $minutes.':'.$seconds;
    }

    public function getFormatedName($length, $name = "") {
        if($name == "") {
            $name = $this->getData('name');
        }
        if(strlen($name) > $length) {
            $name = substr($name, 0, $length).'...';
        }
        return $name;
    }

}
