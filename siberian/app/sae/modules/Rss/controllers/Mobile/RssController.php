<?php

use Rss_Model_Feed as ModelFeed;
use Siberian\Exception;
use Siberian\Json;
use rock\sanitize\Sanitize;

/**
 * Class Rss_Mobile_RssController
 */
class Rss_Mobile_RssController extends Application_Controller_Mobile_Default
{
    /**
     * @var array
     */
    public static $defaultSettings = [
        "design" => "card",
        "aggregation" => "merge",
        "displayThumbnail" => true,
        "displayCover" => true,
        "cacheLifetime" => null,
    ];

    /**
     *
     */
    public function feedsAction()
    {
        try {
            $request = $this->getRequest();
            $valueId = $request->getParam("value_id", null);
            $optionValue = $this->getCurrentOptionValue();
            $refresh = filter_var($request->getParam("refresh", false), FILTER_VALIDATE_BOOLEAN);

            if (!$optionValue->getId()) {
                throw new Exception(p__("rss", "This feature doesn't exists."));
            }

            try {
                $settings = Json::decode($optionValue->getSettings());
                $settings["displayThumbnail"] = (boolean) filter_var($settings["displayThumbnail"], FILTER_VALIDATE_BOOLEAN);
                $settings["displayCover"] = (boolean) filter_var($settings["displayCover"], FILTER_VALIDATE_BOOLEAN);
            } catch (\Exception $e) {
                // Do nothing!
                $settings = [];
            }

            $settings = array_merge(self::$defaultSettings, $settings);

            $cacheId = "rss_feeds_$valueId";
            $cacheTag = "rss_feeds_$valueId";
            $result = $this->cache->load($cacheId);
            if (!$result || $refresh) {
                $feeds = (new ModelFeed())
                    ->findAll(
                        ["value_id = ?" => $valueId],
                        "position ASC"
                    );

                $collection =[];
                foreach ($feeds as $feed) {
                    try {
                        $title = "";
                        $subtitle = "";
                        $thumbnail = "";

                        $feedIo = \FeedIo\Factory::create()->getFeedIo();
                        $result = $feedIo->read($feed->getLink());

                        $title = $result->getFeed()->getTitle();
                        $subtitle = $result->getFeed()->getDescription();

                        // Popping try/catch just for the DOM manipulation
                        try {
                            $xpath = new DOMXpath($result->getDocument()->getDOMDocument());
                            $elements = $xpath->query("//rss//channel//image//url");
                            foreach ($elements as $element) {
                                if (!empty($element->nodeValue)) {
                                    $thumbnail = $element->nodeValue;
                                    break;
                                }
                            }
                        } catch (\Exception $e) {
                            // Jump to next feed if any error occurs!
                            continue;
                        }

                        if (empty($title) || $feed->getReplaceTitle()) {
                            $title = $feed->getTitle();
                        }
                        if (empty($subtitle) || $feed->getReplaceSubtitle()) {
                            $subtitle = $feed->getSubtitle();
                        }
                        if (empty($thumbnail) || $feed->getReplaceThumbnail()) {
                            $thumbnail = $feed->getThumbnail();
                        }

                        $collection[]= [
                            "id" => (integer) $feed->getId(),
                            "title" => (string) $title,
                            "subtitle" => (string) $subtitle,
                            "thumbnail" => (string) $thumbnail,
                        ];

                    } catch (\Exception $e) {
                        // Jump to next feed if any error occurs!
                        continue;
                    }
                }

                $payload = [
                    "success" => true,
                    "page_title" => (string) $optionValue->getTabbarName(),
                    "settings" => $settings,
                    "feeds" => $collection,
                ];

                $cacheLifetime = $settings["cacheLifetime"];
                if ($cacheLifetime === "null") {
                    $cacheLifetime = null;
                }

                $this->cache->save(Json::encode($payload), $cacheId, [
                    "rss",
                    "feedsAction",
                    "value_id_$valueId",
                    $cacheTag
                ], $cacheLifetime);

                $payload["x-cache"] = "MISS";
            } else {
                $payload = Json::decode($result);
                $payload["x-cache"] = "HIT";
            }
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
            $refresh = filter_var($request->getParam("refresh", false), FILTER_VALIDATE_BOOLEAN);

            try {
                $settings = Json::decode($optionValue->getSettings());
                $settings["displayThumbnail"] = (boolean) filter_var($settings["displayThumbnail"], FILTER_VALIDATE_BOOLEAN);
                $settings["displayCover"] = (boolean) filter_var($settings["displayCover"], FILTER_VALIDATE_BOOLEAN);
            } catch (\Exception $e) {
                // Do nothing!
                $settings = [];
            }

            $settings = array_merge(self::$defaultSettings, $settings);

            $cacheId = "rss_grouped_feeds_$valueId";
            $cacheTag = "rss_grouped_feeds_$valueId";
            $result = $this->cache->load($cacheId);
            if (!$result || $refresh) {
                $feeds = (new ModelFeed())->findAll(["value_id = ?" => $valueId]);

                $collection = [];
                foreach ($feeds as $feed) {

                    try {
                        $feedIo = \FeedIo\Factory::create()->getFeedIo();
                        $result = $feedIo->read($feed->getLink());

                        foreach ($result->getFeed() as $item) {
                            $itemArray = $item->toArray();
                            $media = null;
                            $stripMedia = true;
                            foreach ($item->getMedias() as $_media) {
                                $media = $_media->getUrl();
                                $stripMedia = false;
                                break;
                            }

                            $extract = ModelFeed::extract($itemArray["elements"]["content:encoded"], $stripMedia);

                            if (empty($media) && !empty($extract["media"])) {
                                $media = $extract["media"];
                            }

                            if (empty($extract["content"])) {
                                $extract["content"] = $item->getDescription();
                            }

                            $subtitle = Sanitize::removeTags()
                                ->lowercase()
                                ->sanitize($item->getDescription());

                            $collection[] = [
                                "id" => uniqid(),
                                "title" => $item->getTitle(),
                                "subtitle" => cut($subtitle, 60),
                                "subtitle_30" => cut($subtitle, 30),
                                "link" => $item->getLink(),
                                "description" => $item->getDescription(),
                                "content" => $extract["content"],
                                "media" => $media,
                                "author" => $item->getAuthor(),
                                "categories" => $item->getCategories(),
                                "date" => $item->getLastModified(),
                                "timestamp" => ($item->getLastModified()) ?
                                    $item->getLastModified()->getTimestamp() : null,
                            ];
                        }
                    } catch (\Exception $e) {
                        // Jump to next feed if any error occurs!
                        continue;
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

                $cacheLifetime = $settings["cacheLifetime"];
                if ($cacheLifetime === "null") {
                    $cacheLifetime = null;
                }

                $this->cache->save(Json::encode($payload), $cacheId, [
                    "rss",
                    "groupedFeedsAction",
                    "value_id_$valueId",
                    $cacheTag
                ], $cacheLifetime);

                $payload["x-cache"] = "MISS";
            } else {
                $payload = Json::decode($result);
                $payload["x-cache"] = "HIT";
            }
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
            $refresh = filter_var($request->getParam("refresh", false), FILTER_VALIDATE_BOOLEAN);

            try {
                $settings = Json::decode($optionValue->getSettings());
                $settings["displayThumbnail"] = (boolean) filter_var($settings["displayThumbnail"], FILTER_VALIDATE_BOOLEAN);
                $settings["displayCover"] = (boolean) filter_var($settings["displayCover"], FILTER_VALIDATE_BOOLEAN);
            } catch (\Exception $e) {
                // Do nothing!
                $settings = [];
            }

            $settings = array_merge(self::$defaultSettings, $settings);

            $cacheId = "rss_single_feed_$feedId";
            $cacheTag = "rss_single_feed_$feedId";
            $result = $this->cache->load($cacheId);
            if (!$result || $refresh) {
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
                    $stripMedia = true;
                    foreach ($item->getMedias() as $_media) {
                        $media = $_media->getUrl();
                        $stripMedia = false;
                        break;
                    }

                    $extract = ModelFeed::extract($itemArray["elements"]["content:encoded"], $stripMedia);

                    if (empty($media) && !empty($extract["media"])) {
                        $media = $extract["media"];
                    }

                    if (empty($extract["content"])) {
                        $extract["content"] = $item->getDescription();
                    }

                    $subtitle = Sanitize::removeTags()
                        ->lowercase()
                        ->sanitize($item->getDescription());

                    $collection[] = [
                        "id" => uniqid(),
                        "title" => $item->getTitle(),
                        "subtitle" => cut($subtitle, 60),
                        "subtitle_30" => cut($subtitle, 30),
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

                function sortTimestamp($a, $b) {
                    if ($a["timestamp"] == $b["timestamp"]) {
                        return 0;
                    }
                    return ($a["timestamp"] > $b["timestamp"]) ? -1 : 1;
                }
                usort($collection, "sortTimestamp");

                $payload = [
                    "success" => true,
                    "page_title" => (string) $feed->getTitle(),
                    "settings" => $settings,
                    "collection" => $collection,
                ];

                $cacheLifetime = $settings["cacheLifetime"];
                if ($cacheLifetime === "null") {
                    $cacheLifetime = null;
                }

                $this->cache->save(Json::encode($payload), $cacheId, [
                    "rss",
                    "singleFeedAction",
                    "value_id_{$optionValue->getId()}",
                    $cacheTag
                ], $cacheLifetime);

                $payload["x-cache"] = "MISS";
            } else {
                $payload = Json::decode($result);
                $payload["x-cache"] = "HIT";
            }
        } catch (\Exception $e) {
            $payload = [
                "error" => true,
                "message" => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }
}