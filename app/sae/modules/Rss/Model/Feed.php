<?php

class Rss_Model_Feed extends Rss_Model_Feed_Abstract {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Rss_Model_Db_Table_Feed';
        return $this;
    }

    public function updatePositions($positions) {
        $this->getTable()->updatePositions($positions);
        return $this;
    }

    public function getNews() {

        if($this->getId() AND empty($this->_news)) {
            $this->_parse();
        }

        return $this->_news;
    }

    public function copyTo($option) {
        $this->setId(null)->setValueId($option->getId())->save();
        return $this;
    }

    public function getFeaturePaths($option_value) {

        if(!$this->isCachable()) return array();

        $action_view = $this->getActionView();

        $paths = array();

        $params = array(
            'value_id' => $option_value->getId()
        );
        $paths[] = $option_value->getPath("findall", $params, false);

        if($uri = $option_value->getMobileViewUri($action_view)) {

            $feeds = $this->getNews();
            foreach ($feeds->getEntries() as $entry) {
                $feed_id = str_replace("/", "$$", base64_encode($entry->getEntryId()));

                $params = array(
                    "feed_id" => $feed_id,
                    "value_id" => $option_value->getId()
                );
                $paths[] = $option_value->getPath($uri, $params, false);
            }

        }

        return $paths;

    }

}
