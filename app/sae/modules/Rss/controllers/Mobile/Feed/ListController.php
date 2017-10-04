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
            $payload = array();

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
                    $payload['collection'][] = array(
                        "id"        => $feed_id,
                        "url"       => $this->getPath("rss/mobile_feed_view", array(
                            "value_id" => $value_id,
                            "feed_id" => $feed_id)
                        ),
                        "title"     => $entry->getTitle(),
                        "subtitle"  => $author ? html_entity_decode($author) :
                            html_entity_decode($entry->getShortDescription()),
                        "picture"   => $entry->getPicture(),
                        "embed_payload" => array(
                            "id"                    => base64_encode($entry->getEntryId()),
                            "url"                   => $entry->getLink() ? $entry->getLink() : $entry->getEntryId(),
                            "title"                 => $entry->getTitle(),
                            "description"           => $entry->getContent(),
                            "picture"               => $entry->getPicture(),
                            "date"                  => $entry->getUpdatedAt(),
                            "social_sharing_active" => (boolean) $this->getCurrentOptionValue()->getSocialSharingIsActive()
                        )
                    );
                }
            }

            if(!empty($payload['collection'][0]) AND !empty($payload['collection'][0]["picture"])) {
                $payload["cover"] = $payload['collection'][0];
                $payload['collection'] = array_slice($payload['collection'], 1, count($payload['collection'])-1);
            }

            $payload['page_title'] = $this->getCurrentOptionValue()->getTabbarName();

            $this->_sendJson($payload);
        }

    }



}