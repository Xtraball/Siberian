<?php

class Media_Model_Gallery_Music_Type_Podcast extends Core_Model_Default {

    public function __construct($params = array()) {
        parent::__construct($params);
        return $this;
    }

    public function parse() {

        $feed = Zend_Feed_Reader::import($this->getFeedUrl());

        $image_uri = null;
        $image = $feed->getImage();
        if(is_array($image) AND !empty($image["uri"])) $image_uri = $image["uri"];

        $data = array(
            "title" => $feed->getTitle(),
            "description" => $feed->getDescription() ? $feed->getDescription() : $feed->getSubtitle(),
            "author" => $feed->getExtension('Podcast')->getCastAuthor(),
            "image" => $image_uri,
            "last_update" => $feed->getLastBuildDate() ? $feed->getLastBuildDate()->toString("dd/MM/yy") : null,
            "items" => array(),
            "number_of_tracks" => 0
        );

        foreach($feed as $entry) {
            $podcast = $entry->getExtension('Podcast');

            $data["items"][] = array(
                "title" => $entry->getTitle(),
                "stream_url" => $entry->getEnclosure()->url,
                "formatted_duration" => $podcast->getDuration(),
                "duration" => $podcast->getDuration() ? $this->_calcDuration($podcast->getDuration()) : null
            );
        }

        $data["number_of_tracks"] = count($data["items"]);

        return $data;

    }

    protected function _calcDuration($duration) {

        $hours = 0;
        $minutes = 0;
        $seconds = 0;

        try {
            list($hours, $minutes, $seconds) = explode(":", $duration);
        } catch(Exception $e) {

        }

        return (($hours * 3600) + ($minutes * 60) + $seconds) * 1000;
    }

}
