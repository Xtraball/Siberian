<?php

use Siberian\Exception;

/**
 * Class Rss_Mobile_RssController
 */
class Rss_Mobile_RssController extends Application_Controller_Mobile_Default
{
    /**
     *
     */
    public function listAction()
    {
        try {
            $request = $this->getRequest();
            $feedId = $request->getParam("feedId", null);

            $feed = (new Rss_Model_Feed())->find($feedId);

            if (!$feed->getId()) {
                throw new Exception(p__("rss", "This feed doesn't exists."));
            }

            $feedIo = \FeedIo\Factory::create()->getFeedIo();
            $result = $feedIo->read($feed->getLink());

            $collection = [];

            foreach ($result->getFeed() as $item) {
                $itemArray = $item->toArray();
                $medias = [];
                foreach ($item->getMedias() as $media) {
                    $medias[] = $media;
                }

                if (empty($medias)) {
                    $medias[] = Rss_Model_Feed::extractMedia($itemArray["elements"]["content:encoded"]);
                }
                
                $collection[] = [
                    "title" => $item->getTitle(),
                    "link" => $item->getLink(),
                    "description" => $item->getDescription(),
                    "content" => $itemArray["elements"]["content:encoded"],
                    "medias" => $medias,
                    "author" => $item->getAuthor(),
                    "categories" => $item->getCategories(),
                    "date" => $item->getLastModified(),
                ];
            }

            $payload = [
                "success" => true,
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
    public function feedsAction()
    {
        try {
            $request = $this->getRequest();
            $valueId = $request->getParam("value_id", null);
            $optionValue = $this->getCurrentOptionValue();

            if (!$optionValue->getId()) {
                throw new Exception(p__("rss", "This feature doesn't exists."));
            }

            $feeds = (new Rss_Model_Feed())
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