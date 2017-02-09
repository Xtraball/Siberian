<?php

abstract class Rss_Model_Feed_Abstract extends Core_Model_Default {

    protected $_news = array();

    protected function _parse() {

        $feed = Zend_Feed_Reader::import($this->getLink());
        $this->_news = new Core_Model_Default(array(
            'title'        => $feed->getTitle(),
            'link'         => $feed->getLink(),
            'dateModified' => $feed->getDateModified(),
            'description'  => $feed->getDescription(),
            'language'     => $feed->getLanguage(),
            'entries'      => array(),
        ));

        $data = array();
        foreach ($feed as $entry) {
            $picture = null;
            if($entry->getEnclosure() && $entry->getEnclosure()->url) $picture = $entry->getEnclosure()->url;

            $description = "";
            if($entry->getContent()) {
                $content = new Dom_SmartDOMDocument();
                $content->loadHTML($entry->getContent());
                $content->encoding = 'utf-8';
                $description = $content->documentElement;
                $imgs = $description->getElementsByTagName('img');

                if($imgs->length > 0) {

                    foreach($imgs as $k => $img) {
                        if($k == 0) {

                            $img = $imgs->item(0);

                            if($img->getAttribute('src') AND stripos($img->getAttribute('src'), ".gif") === false) {
                                $picture = $img->getAttribute('src');
                                $img->parentNode->removeChild($img);
                            }

                        }

                        $img->removeAttribute('width');
                        $img->removeAttribute('height');
                    }

                }

                $as = $description->getElementsByTagName('a');

                if($as->length > 0) {

                    foreach($as as $a) {
                        $a->setAttribute('target', '_self');
                    }
                }

                $description = $content->saveHTMLExact();
            }

            $timestamp = $entry->getDateCreated() ? $entry->getDateCreated()->getTimestamp() : null;
            $updated_at = null;
            if($timestamp) {
                $updated_at = $this->_getUpdatedAt($timestamp);
            }


            $picture_ext = var_dump(pathinfo(parse_url($picture, PHP_URL_PATH), PATHINFO_EXTENSION));
            if(!in_array($picture_ext, array("gif", "png", "jpeg", "jpg")))
                $picture = null;

            $edata = new Core_Model_Default(array(
                'entry_id'     => $entry->getId(),
                'title'        => $entry->getTitle(),
                'description'  => $description,
                'short_description'  => strip_tags($description),
                'dateModified' => $entry->getDateModified(),
                'authors'      => $entry->getAuthors(),
                'link'         => $entry->getLink(),
                'content'      => $description,
                'enclosure'    => $entry->getEnclosure(),
                'timestamp'    => $timestamp,
                'updated_at'   => $updated_at,
                'picture'      => $picture,
            ));

            $data[] = $edata;
        }

        $this->_news->setEntries($data);

        return $this;
    }

    protected function _getUpdatedAt($timestamp) {

        $date = new Zend_Date($timestamp);
        $now = Zend_Date::now();
        $difference = $now->sub($date);

        $seconds = $difference->toValue() % 60; $allMinutes = ($difference->toValue() - $seconds) / 60;
        $minutes = $allMinutes % 60; $allHours = ($allMinutes - $minutes) / 60;
        $hours =  $allHours % 24; $days = ($allHours - $hours) / 24;

        switch($days) {
            case 0: $days   = false; break;
            case 1: $days  .= " {$this->_('day')}"; break;
            default: $days .= " {$this->_('days')}"; break;
        }
        switch($hours) {
            case 0: $hours   = false; break;
            case 1: $hours  .= " {$this->_('hour')}"; break;
            default: $hours .= " {$this->_('hours')}"; break;
        }
        switch($minutes) {
            case 0: $minutes   = false; break;
            case 1: $minutes  .= " {$this->_('minute')}"; break;
            default: $minutes .= " {$this->_('minutes')}"; break;
        }

        $updated_at = '';
        if($days) {
            $updated_at = $days;
        } elseif($hours) {
            $updated_at = $hours;
        } elseif($minutes) {
            $updated_at = $minutes;
        }

        return $this->_('%s ago', $updated_at);

    }

}
