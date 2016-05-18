<?php

class Rss_Mobile_Feed_ListController extends Application_Controller_Mobile_Default {

    public function indexAction() {
        $this->forward('index', 'index', 'Front', $this->getRequest()->getParams());
    }

    public function templateAction() {
        $this->loadPartials($this->getFullActionName('_').'_l'.$this->_layout_id, false);
    }

    public function findallAction() {

        if($value_id = $this->getRequest()->getParam('value_id')) {

            $rss_feed = new Rss_Model_Feed();
            $rss_feeds = $rss_feed->findAll(array('value_id' => $value_id), 'position ASC');
            $data = array();

            foreach($rss_feeds as $rss_feed) {

                $news = $rss_feed->getNews();
                foreach($news->getEntries() as $entry) {

                    $author = "";
                    $authors = array();
                    if($entry->getAuthors()) {
                        foreach ($entry->getAuthors() as $author) {
                            $authors[] = $author["name"];
                        }

                        if (!empty($authors)) {
                            $author = implode(", ", $authors);
                        }
                    }

                    $feed_id = str_replace("/", "$$", base64_encode($entry->getEntryId()));
                    $data['collection'][] = array(
                        "id" => $feed_id,
                        "url" => $this->getPath("rss/mobile_feed_view", array("value_id" => $value_id, "feed_id" => $feed_id)),
                        "title" => $entry->getTitle(),
                        "subtitle" => $author ? html_entity_decode($entry->getTitle()) : html_entity_decode($entry->getShortDescription()),
                        "picture" => $entry->getPicture()
                    );
                }
            }

            if(!empty($data['collection'][0]) AND !empty($data['collection'][0]["picture"])) {
                $data["cover"] = $data['collection'][0];
                $data['collection'] = array_slice($data['collection'], 1, count($data['collection'])-1);
            }

            $data['page_title'] = $this->getCurrentOptionValue()->getTabbarName();

            $this->_sendHtml($data);
        }

    }

//    protected function _getUpdatedAt($entry) {
//
//        if(!$entry->getTimestamp()) return null;
//
//        $date = new Zend_Date($entry->getTimestamp());
//        $now = Zend_Date::now();
//        $difference = $now->sub($date);
//
//        $seconds = $difference->toValue() % 60; $allMinutes = ($difference->toValue() - $seconds) / 60;
//        $minutes = $allMinutes % 60; $allHours = ($allMinutes - $minutes) / 60;
//        $hours =  $allHours % 24; $allDays = ($allHours - $hours) / 24;
//        $allDays.= ' ';
//        $hours.= ' ';
//        $minutes.= ' ';
//
//        if($allDays > 0) {
//            $allDays .= $this->_('day');
//            if($allDays > 1) {
//                $allDays .= "s";
//            }
//        } else {
//            $allDays = '';
//        }
//        if($hours > 0) {
//            $hours .= $this->_('hour');
//            if($hours > 1) {
//                $hours .= "s";
//            }
//        } else {
//            $hours = '';
//        }
//        if($minutes > 0) {
//            $minutes .= $this->_('minute');
//            if($minutes > 1) {
//                $minutes .= "s";
//            }
//        } else {
//            $minutes = '';
//        }
//
//        $updated_at = '';
//        if($allDays != '') {
//            $updated_at = $allDays;
//        } elseif($hours != '') {
//            $updated_at = $hours;
//        } elseif($minutes != '') {
//            $updated_at = $minutes;
//        }
//
//        return $updated_at;
//
//    }


}