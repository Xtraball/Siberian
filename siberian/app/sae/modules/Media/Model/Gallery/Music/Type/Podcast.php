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

    /**
     * Duration in milliseconds from an iTunes duration: "h:m:s", "m:s" or plain seconds (decimals allowed).
     * Anything that is not numeric gives null: PHP 8 throws a TypeError on "abc" * 60.
     *
     * @param mixed $duration
     * @return int|null
     */
    protected function _calcDuration($duration)
    {
        $parts = array_reverse(explode(":", trim((string) $duration)));
        if (count($parts) > 3) {
            return null;
        }

        $seconds = 0;
        foreach ($parts as $index => $part) {
            $part = trim($part);
            if (!is_numeric($part) || $part < 0) {
                return null;
            }
            $seconds += $part * (60 ** $index);
        }

        return (int) round($seconds * 1000);
    }

}
