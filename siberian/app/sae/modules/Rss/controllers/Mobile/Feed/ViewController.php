<?php

class Rss_Mobile_Feed_ViewController extends Application_Controller_Mobile_Default {

    public function indexAction() {
        $this->forward('index', 'index', 'Front', $this->getRequest()->getParams());
    }

    public function templateAction() {
        $this->loadPartials($this->getFullActionName('_').'_l'.$this->_layout_id, false);
    }

    /**
     * @deprecated in Siberian 5.0
     */
    public function findAction() {

        if($value_id = $this->getRequest()->getParam('value_id') AND $feed_id = $this->getRequest()->getParam('feed_id')) {

            $rss_feed = new Rss_Model_Feed();
            $rss_feeds = $rss_feed->findAll(array('value_id' => $value_id), 'position ASC');
            $data = array('feed' => array());
            $feed_id = base64_decode(str_replace("$$", "/", $feed_id));

            foreach($rss_feeds as $rss_feed) {

                $option = $this->getCurrentOptionValue();

                $news = $rss_feed->getNews();
                foreach($news->getEntries() as $entry) {

                    if($feed_id == $entry->getEntryId()) {

                        $data = array(
                            "id" => base64_encode($entry->getEntryId()),
                            "url" => $entry->getLink() ? $entry->getLink() : $entry->getEntryId(),
                            "title" => $entry->getTitle(),
                            "description" => $entry->getContent(),
                            "picture" => $entry->getPicture(),
                            "date" => $entry->getUpdatedAt(),
                            "social_sharing_active" => (boolean) $option->getSocialSharingIsActive()
                        );

                    }

                }
            }

            $this->_sendHtml($data);
        }

    }

}