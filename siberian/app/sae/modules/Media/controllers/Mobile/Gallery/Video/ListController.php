<?php

use Siberian\Exception;
use Siberian\Request;
use Siberian\Json;

/**
 * Class Media_Mobile_Gallery_Video_ListController
 */
class Media_Mobile_Gallery_Video_ListController extends Application_Controller_Mobile_Default
{

    /**
     * @throws Zend_Exception
     */
    public function findallAction()
    {

        if ($value_id = $this->getRequest()->getParam('value_id')) {

            try {

                $videos = (new Media_Model_Gallery_Video())
                    ->findAll(['value_id' => $value_id]);
                $payload = [
                    'collection' => []
                ];

                foreach ($videos as $video) {
                    $payload['collection'][] = [
                        'id' => $video->getId(),
                        'name' => $video->getName(),
                        'type' => $video->getTypeId(),
                        'search_by' => $video->getType(),
                        'search_keyword' => $video->getParam()
                    ];
                    if ($video->getTypeId() === 'youtube') {
                        $has_youtube_videos = true;
                    }
                }

                $payload['page_title'] = $this->getCurrentOptionValue()->getTabbarName();
                $payload['displayed_per_page'] = Media_Model_Gallery_Video_Abstract::DISPLAYED_PER_PAGE;
                $payload['header_right_button']['picto_url'] = $this->_getColorizedImage($this->_getImage('pictos/more.png', true),
                    $this->getApplication()->getBlock('subheader')->getColor());
            } catch (Exception $e) {
                $payload = [
                    'error' => true,
                    'message' => $e->getMessage()
                ];
            }

            $this->_sendJson($payload);
        }
    }

    public function proxyYoutubeAction()
    {
        try {
            $request = $this->getRequest();
            $params = $request->getBodyParams();
            $application = $this->getApplication();
            $youtubeKey = $application->getYoutubeKey();

            if (empty($youtubeKey)) {
                throw new Exception('Missing and/or incorrect API key.');
            }

            $results = $this->fetchYoutube($youtubeKey,
                $params['type'], $params['keyword'], $params['offset']);

            //
            $payload = [
                'success' => true,
                'collection' => $results['collection'],
                'nextPageToken' => $results['nextPageToken'],
            ];

        } catch (\Exception $e) {
            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $this->_sendJson($payload);
    }

    /**
     * @param $youtubeKey
     * @param $type
     * @param $keyword
     * @param $offset
     * @return array
     * @throws Exception
     */
    public function fetchYoutube ($youtubeKey, $type, $keyword, $offset): array
    {
        switch ($type) {
            case 'search':
                $endpoint = 'https://www.googleapis.com/youtube/v3/search/';
                $params = [
                    'q' => $keyword,
                    'type' => 'video',
                    'part' => 'snippet',
                    'key' => $youtubeKey,
                    'maxResults' => '5',
                    'order' => 'date'
                ];
                if (!empty($offset)) {
                    $params['pageToken'] = $offset;
                }
                $response = Request::get($endpoint, $params);
                $results = Json::decode($response);
                return $this->resultsToCollection($results);

                break;

            case 'channel':
                $endpoint = 'https://www.googleapis.com/youtube/v3/channels/';
                $params = [
                    'forUsername' => $keyword,
                    'part' => 'snippet',
                    'key' => $youtubeKey,
                    'maxResults' => '5',
                    'order' => 'date'
                ];
                $response = Request::get($endpoint, $params);
                $results = Json::decode($response);
                if (count($results['items']) === 0) {
                    $channelId = $keyword;
                } else {
                    $channelId = $results['items'][0]['id'];
                }

                if (empty($channelId)) {
                    throw new Exception(p__('media', 'No results.'));
                }

                $endpoint = 'https://www.googleapis.com/youtube/v3/search/';
                $params = [
                    'channelId' => $channelId,
                    'type' => 'video',
                    'part' => 'snippet',
                    'key' => $youtubeKey,
                    'maxResults' => '5',
                    'order' => 'date'
                ];
                if (!empty($offset)) {
                    $params['pageToken'] = $offset;
                }
                $response = Request::get($endpoint, $params);
                $results = Json::decode($response);
                return $this->resultsToCollection($results);

                break;

            case 'user':
                $endpoint = 'https://www.googleapis.com/youtube/v3/channels/';
                $params = [
                    'forUsername' => $keyword,
                    'part' => 'contentDetails',
                    'key' => $youtubeKey,
                    'maxResults' => '5',
                    'order' => 'date'
                ];
                $response = Request::get($endpoint, $params);
                $results = Json::decode($response);
                try {
                    $playlistId = $results['items'][0]['contentDetails']['relatedPlaylists']['uploads'];
                } catch (\Exception $e) {
                    throw new Exception(p__('media', 'No results.'));
                }

                $endpoint = 'https://www.googleapis.com/youtube/v3/playlistItems/';
                $params = [
                    'playlistId' => $playlistId,
                    'part' => 'snippet',
                    'key' => $youtubeKey,
                    'maxResults' => '5',
                    'order' => 'date'
                ];
                if (!empty($offset)) {
                    $params['pageToken'] = $offset;
                }
                $response = Request::get($endpoint, $params);
                $results = Json::decode($response);
                return $this->resultsToCollection($results);

                break;

        }

        return [
            'collection' => [],
            'nextPageToken' => '',
        ];
    }

    /**
     * @param $results
     * @return array
     * @throws Exception
     */
    public function resultsToCollection ($results): array
    {
        if (!array_key_exists('items', $results)) {
            throw new Exception(p__('media', 'No results.'));
        }

        $collection = [];
        foreach ($results['items'] as $item) {
            $videoId = $item['id']['videoId'] ?? $item['snippet']['resourceId']['videoId'];
            $collection[] = [
                'video_id' => $videoId,
                'cover_url' => $item['snippet']['thumbnails']['medium']['url'],
                'title' => $item['snippet']['title'],
                'description' => $item['snippet']['description'],
                'date' => $item['snippet']['publishedAt'],
                'url' => 'https://www.youtube.com/watch?v=' . $videoId,
                'url_embed' => 'https://www.youtube.com/embed/' . $videoId,
            ];
        }
        $nextPageToken = $results['nextPageToken'];

        return [
            'collection' => $collection,
            'nextPageToken' => $nextPageToken,
        ];
    }

}
