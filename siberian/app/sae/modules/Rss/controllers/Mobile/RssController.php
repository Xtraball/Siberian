<?php

use Siberian\Exception;
use Siberian\Json;
use Rss_Model_Feed as ModelFeed;
use rock\sanitize\Sanitize;

/**
 * Class Rss_Mobile_RssController
 */
class Rss_Mobile_RssController extends Application_Controller_Mobile_Default
{
    /**
     *
     */
    public function feedsAction()
    {
        try {
            $request = $this->getRequest();
            $valueId = $request->getParam("value_id", null);
            $optionValue = $this->getCurrentOptionValue();

            try {
                $settings = Json::decode($optionValue->getSettings());
                $settings["displayThumbnail"] = (boolean) filter_var($settings["displayThumbnail"], FILTER_VALIDATE_BOOLEAN);
                $settings["displayCover"] = (boolean) filter_var($settings["displayCover"], FILTER_VALIDATE_BOOLEAN);
            } catch (\Exception $e) {
                $settings = [
                    "design" => "card",
                    "displayThumbnail" => true,
                    "displayCover" => true,
                ];
            }

            if (!$optionValue->getId()) {
                throw new Exception(p__("rss", "This feature doesn't exists."));
            }

            $feeds = (new ModelFeed())
                ->findAll(
                    ["value_id = ?" => $valueId],
                    "position ASC"
                );

            $collection =[];
            foreach ($feeds as $feed) {
                $collection[]= [
                    "id" => (integer) $feed->getId(),
                    "title" => (string) $feed->getTitle(),
                    "subtitle" => (string) $feed->getSubtitle(),
                    "thumbnail" => (string) $feed->getThumbnail(),
                ];
            }

            $payload = [
                "success" => true,
                "page_title" => (string) $optionValue->getTabbarName(),
                "settings" => $settings,
                "feeds" => $collection,
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function groupedFeedsAction()
    {
        try {
            $request = $this->getRequest();
            $valueId = $request->getParam("value_id", null);
            $optionValue = $this->getCurrentOptionValue();

            try {
                $settings = Json::decode($optionValue->getSettings());
                $settings["displayThumbnail"] = (boolean) filter_var($settings["displayThumbnail"], FILTER_VALIDATE_BOOLEAN);
                $settings["displayCover"] = (boolean) filter_var($settings["displayCover"], FILTER_VALIDATE_BOOLEAN);
            } catch (\Exception $e) {
                $settings = [
                    "design" => "card",
                    "displayThumbnail" => true,
                    "displayCover" => true,
                ];
            }

            $feeds = (new ModelFeed())->findAll(["value_id = ?" => $valueId]);

            $collection = [];
            foreach ($feeds as $feed) {

                try {
                    $feedIo = \FeedIo\Factory::create()->getFeedIo();
                    $result = $feedIo->read($feed->getLink());
                } catch (\Exception $e) {
                    // Jump to next feed if any error occurs!
                    continue;
                }

                foreach ($result->getFeed() as $item) {
                    $itemArray = $item->toArray();
                    $media = null;
                    foreach ($item->getMedias() as $_media) {
                        $media = $_media;
                        break;
                    }

                    $extract = ModelFeed::extract($itemArray["elements"]["content:encoded"]);

                    if (empty($media) && !empty($extract["media"])) {
                        $media = $extract["media"];
                    }

                    $subtitle = Sanitize::removeTags()
                        ->lowercase()
                        ->sanitize($item->getDescription());

                    $collection[] = [
                        "id" => uniqid(),
                        "title" => $item->getTitle(),
                        "subtitle" => cut($subtitle, 120),
                        "link" => $item->getLink(),
                        "description" => $item->getDescription(),
                        "content" => $extract["content"],
                        "media" => $media,
                        "author" => $item->getAuthor(),
                        "categories" => $item->getCategories(),
                        "date" => $item->getLastModified(),
                        "timestamp" => $item->getLastModified()->getTimestamp(),
                    ];
                }
            }

            function sortTimestamp($a, $b) {
                if ($a["timestamp"] == $b["timestamp"]) {
                    return 0;
                }
                return ($a["timestamp"] > $b["timestamp"]) ? -1 : 1;
            }
            usort($collection, "sortTimestamp");

            $payload = [
                "success" => true,
                "page_title" => (string) $optionValue->getTabbarName(),
                "settings" => $settings,
                "collection" => $collection,
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     *
     */
    public function singleFeedAction()
    {
        try {
            $request = $this->getRequest();
            $feedId = $request->getParam("feedId", null);
            $optionValue = $this->getCurrentOptionValue();

            try {
                $settings = Json::decode($optionValue->getSettings());
                $settings["displayThumbnail"] = (boolean) filter_var($settings["displayThumbnail"], FILTER_VALIDATE_BOOLEAN);
                $settings["displayCover"] = (boolean) filter_var($settings["displayCover"], FILTER_VALIDATE_BOOLEAN);
            } catch (\Exception $e) {
                $settings = [
                    "design" => "card",
                    "displayThumbnail" => true,
                    "displayCover" => true,
                ];
            }

            $feed = (new ModelFeed())->find($feedId);

            if (!$feed->getId()) {
                throw new Exception(p__("rss", "This feed doesn't exists."));
            }

            $feedIo = \FeedIo\Factory::create()->getFeedIo();
            $result = $feedIo->read($feed->getLink());

            $collection = [];

            foreach ($result->getFeed() as $item) {
                $itemArray = $item->toArray();
                $media = null;
                foreach ($item->getMedias() as $_media) {
                    $media = $_media;
                    break;
                }

                $extract = ModelFeed::extract($itemArray["elements"]["content:encoded"]);

                if (empty($media) && !empty($extract["media"])) {
                    $media = $extract["media"];
                }

                $subtitle = Sanitize::removeTags()
                    ->lowercase()
                    ->sanitize($item->getDescription());
                
                $collection[] = [
                    "id" => uniqid(),
                    "title" => $item->getTitle(),
                    "subtitle" => cut($subtitle, 120),
                    "link" => $item->getLink(),
                    "description" => $item->getDescription(),
                    "content" => $extract["content"],
                    "media" => $media,
                    "author" => $item->getAuthor(),
                    "categories" => $item->getCategories(),
                    "date" => $item->getLastModified(),
                    "timestamp" => $item->getLastModified()->getTimestamp(),
                ];
            }

            $payload = [
                "success" => true,
                "page_title" => (string) $feed->getTitle(),
                "settings" => $settings,
                "collection" => $collection,
            ];
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }
}