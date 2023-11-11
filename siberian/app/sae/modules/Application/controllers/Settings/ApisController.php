<?php

/**
 * Class Application_Settings_ApisController
 */
class Application_Settings_ApisController extends Application_Controller_Default
{
    /**
     *
     */
    public function indexAction()
    {
        $this->loadPartials();
    }

    /**
     *
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {

            try {

                $application = $this->getApplication();

                if ($this->_canAccess('editor_settings_onesignal')) {
                    $application
                        ->setOnesignalAppId($data['onesignal_app_id'])
                        ->setOnesignalAppKeyToken($data['onesignal_app_key_token'])
                        ->setOnesignalDefaultSegment($data['onesignal_default_segment']);
                }

                if ($this->_canAccess('editor_settings_twitter')) {
                    $application
                        ->setTwitterConsumerKey($data['twitter_consumer_key'])
                        ->setTwitterConsumerSecret($data['twitter_consumer_secret'])
                        ->setTwitterApiToken($data['twitter_api_token'])
                        ->setTwitterApiSecret($data['twitter_api_secret']);
                }

                if ($this->_canAccess('editor_settings_instagram')) {
                    $application
                        ->setInstagramClientId($data['instagram_client_id'])
                        ->setInstagramToken($data['instagram_token']);
                }

                if ($this->_canAccess('editor_settings_flickr')) {
                    $application
                        ->setFlickrKey($data['flickr_key'])
                        ->setFlickrSecret($data['flickr_secret']);
                }

                if (!empty($data['googlemaps_key'])) {
                    Siberian_Google_Geocoding::testApiKey($data['googlemaps_key']);

                    $application
                        ->setGooglemapsKey($data['googlemaps_key']);
                } else {
                    $application->setGooglemapsKey('');
                }

                if (!empty($data['youtube_key'])) {
                    $this->testYoutubeKey($data['youtube_key']);

                    $application
                        ->setYoutubeKey($data['youtube_key']);
                } else {
                    $application->setYoutubeKey('');
                }

                if (!empty($data['openweathermap_key'])) {
                    Weather_Model_Weather::testApiKey($data['openweathermap_key']);

                    $application
                        ->setOwmKey($data['openweathermap_key']);
                } else {
                    $application
                        ->setOwmKey('');
                }

                if (!empty($data['ipinfo_key'])) {
                    $application
                        ->setIpinfoKey($data['ipinfo_key']);
                } else {
                    $application
                        ->setIpinfoKey('');
                }


                $application->save();

                $payload = [
                    'success' => true,
                    'success_message' => __('Option successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0,
                ];

            } catch (\Exception $e) {
                $payload = [
                    'error' => true,
                    'message' => $e->getMessage(),
                ];
            }

            $this->_sendJson($payload);
        }
    }

    /**
     * @param $key
     * @return bool
     * @throws \Siberian\Exception
     */
    private function testYoutubeKey($key): bool
    {
        $key = trim($key);
        if (empty($key)) {
            throw new \Siberian\Exception('#808-101 [YouTube]: ' . __('Missing and/or empty API key.'));
        }

        $response = \Siberian_Request::get('https://www.googleapis.com/youtube/v3/search/', [
            'q' => 'test',
            'type' => 'video',
            'part' => 'snippet',
            'key' => $key,
            'maxResults' => '5',
            'order' => 'date'
        ]);

        if (empty($response)) {
            throw new \Siberian\Exception('#808-102 [YouTube]: ' . __('Something went wrong with the API.'));
        }

        $result = \Siberian_Json::decode($response);

        if (array_key_exists('error', $result)) {
            throw new \Siberian\Exception('#808-104 [YouTube]: ' . $result['error']['errors'][0]['reason']);
        }

        return true;
    }
}
