<?php

class Media_Model_Gallery_Music_Album extends Core_Model_Default {

    protected $_tracks = array();

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Media_Model_Db_Table_Gallery_Music_Album';
        return $this;
    }

    public function getNextAlbumPosition() {
        $lastPosition = $this->getTable()->getLastAlbumPosition();
        if(!$lastPosition) $lastPosition = 0;

        return ++$lastPosition;
    }

     public function getAllTracks() {

        if(!$this->_tracks) {

            if($this->getType() == "podcast") {
                $podcast = new Media_Model_Gallery_Music_Type_Podcast();
                $data = $podcast->setFeedUrl($this->getPodcastUrl())->parse();
                foreach($data["items"] as $item) {
                    $track = new Media_Model_Gallery_Music_Track();
                    $track->setName($item["title"])
                        ->setDuration($item["duration"])
                        ->setFormattedDuration($item["formatted_duration"])
                        ->setStreamUrl($item["stream_url"])
                        ->setType('podcast');
                    $this->_tracks[] = $track;
                }
            } else {
                $tracks = new Media_Model_Gallery_Music_Track();
                $tracks = $tracks->findAll(array('album_id' => $this->getId()), 'position ASC');
                foreach ($tracks as $track) {
                    $this->_tracks[] = $track;
                }
            }
        }
        return $this->_tracks;
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

    public function getTotalTracks() {
        $total_tracks = $this->getAllTracks(true);
        return count($total_tracks);
    }

    public function getTotalDuration() {

        $total_tracks = $this->getAllTracks(true);
        $total_duration = 0;
        $return = array();

        foreach($total_tracks as $track) {
            $total_duration += $track->getDuration();
        }

        // Seconds
        $total_duration = floor($total_duration / 1000);
        $seconds = $total_duration % 60;
        $total_duration = floor($total_duration / 60);

        // Minutes
        $minutes = $total_duration % 60;
        $total_duration = floor($total_duration / 60);

        // Hours
        $hours = $total_duration % 60;
        $total_duration = floor($total_duration / 60);


        if($hours > 0) {
            $return[] = str_pad($hours, 2, 0, STR_PAD_LEFT);
        }
        if($hours > 0 OR $minutes > 0) {
            $return[] = str_pad($minutes, 2, 0, STR_PAD_LEFT);
        }

        $return[] = str_pad($seconds, 2, 0, STR_PAD_LEFT);


        return implode(":", $return);
    }

    public function getFormatedName() {
        $name = $this->getData('name');
        $name = utf8_decode($name);
        if(strlen($name) > 18) {
            $name = substr($name, 0, 18).'...';
        }
        $name = utf8_encode($name);
        return $name;
    }

    /** API v2 introduced in Siberian 5.0 with Progressive Web Apps. */
    public static function _toJson($value_id, $album) {

        $total_duration     = $album->getTotalDuration();
        $total_tracks       = $album->getTotalTracks();
        $url = __path("media/mobile_gallery_music_album/index", array(
            "value_id" => $value_id,
            "album_id" => $album->getId()
        ));
        $element = "album";

        $artwork_url = $album->getArtworkUrl();
        if(stripos($artwork_url, "http") === false) {
            $artwork_url = Core_Model_Directory::getBasePathTo($artwork_url);
        }
        $artwork_image = Siberian_Image::open($artwork_url)->cropResize(256)->inline();

        $json = array(
            "id"                => $album->getId(),
            "name"              => $album->getName(),
            "artworkUrl"        => $artwork_image,
            "artistName"        => $album->getArtistName(),
            "totalDuration"     => $total_duration,
            "totalTracks"       => $total_tracks,
            "path"              => $url,
            "type"              => $album->getType(),
            "element"           => $element
        );

        return $json;
    }

}
