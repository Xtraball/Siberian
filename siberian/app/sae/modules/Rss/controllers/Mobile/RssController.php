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
            $valueId = $request->getParam("valueId");
            $feedId = $request->getParam("feedId");

            $feed = (new Rss_Model_Feed())->find($feedId);

            if (!$feed->getId()) {
                throw new Exception(p__("rss", "This feed doesn't exists."));
            }

            $feedIo = \FeedIo\Factory::create()->getFeedIo();
            $result = $feedIo->read($feed->getLink());

            $collection = [];

            echo "<pre>";
            foreach ($result->getFeed() as $item) {
                $medias = [];
                foreach ($item->getMedias() as $media) {
                    echo get_class($media);
                }
                
                $collection[] = [
                    "title" => $item->getTitle(),
                    "link" => $item->getLink(),
                    "description" => $item->getDescription(),
                    "medias" => 1,
                    "author" => $item->getAuthor(),
                    "categories" => $item->getCategories(),
                    "date" => $item->getLastModified(),
                    "raw" => print_r($item, 1),
                ];
            }
            die;

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