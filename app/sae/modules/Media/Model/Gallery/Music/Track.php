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

    /** API v2 introduced in Siberian 5.0 with Progressive Web Apps. */
    public static function _toJson($track) {

        $album_cover = $track->getArtworkUrl();
        if(stripos($album_cover, "http") === false) {
            if(!file_exists(Core_Model_Directory::getBasePathTo($album_cover)) || ($album_cover === "/images/library/musics/default_album.jpg")) {
                $album_cover_b64 = null;
            } else {
                $album_cover = Core_Model_Directory::getBasePathTo($album_cover);
                $album_cover_b64 = Siberian_Image::open($album_cover)->cropResize(64)->inline();
            }
        }

        $json = array(
            "id"                => $track->getId(),
            "name"              => $track->getName(),
            "artistName"        => $track->getArtistName(),
            "albumName"         => $track->getAlbumName(),
            "albumCover"        => $album_cover_b64,
            "albumId"           => $track->getAlbumId(),
            "duration"          => $track->getDuration(),
            "streamUrl"         => $track->getStreamUrl(),
            "purchaseUrl"       => $track->getPurchaseUrl()
        );

        if($track->getType() !== "podcast") {
            if(($track->getType() === "itunes") && ($track->getPrice() > 0)) {
                $json["duration"] = "29000";
            }

            $json["formatedDuration"] = $track->getFormatedDuration($track->getDuration());

            if($track->getType() === "soundcloud") {
                $json["streamUrl"] = $json["streamUrl"]."?client_id=".Api_Model_Key::findKeysFor("soundcloud")->getClientId();
            }

        } else {
            $json["formatedDuration"] = $track->getFormatedDuration();
        }

        return $json;
    }

}
