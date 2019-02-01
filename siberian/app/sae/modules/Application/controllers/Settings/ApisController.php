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

                if ($this->_canAccess('editor_settings_facebook')) {
                    $application
                        ->setFacebookId($data["facebook_id"])
                        ->setFacebookKey($data["facebook_key"]);
                }

                if ($this->_canAccess('editor_settings_twitter')) {
                    $application
                        ->setTwitterConsumerKey($data["twitter_consumer_key"])
                        ->setTwitterConsumerSecret($data["twitter_consumer_secret"])
                        ->setTwitterApiToken($data["twitter_api_token"])
                        ->setTwitterApiSecret($data["twitter_api_secret"]);
                }

                if ($this->_canAccess('editor_settings_instagram')) {
                    $application
                        ->setInstagramClientId($data["instagram_client_id"])
                        ->setInstagramToken($data["instagram_token"]);
                }

                if ($this->_canAccess('editor_settings_flickr')) {
                    $application
                        ->setFlickrKey($data["flickr_key"])
                        ->setFlickrSecret($data["flickr_secret"]);
                }

                if (!empty($data["googlemaps_key"])) {
                    Siberian_Google_Geocoding::testApiKey($data["googlemaps_key"]);

                    $application
                        ->setGooglemapsKey($data["googlemaps_key"]);
                } else {
                    $application->setGooglemapsKey("");
                }

                if (!empty($data["openweathermap_key"])) {
                    Weather_Model_Weather::testApiKey($data["openweathermap_key"]);

                    $application
                        ->setOwmKey($data["openweathermap_key"]);
                } else {
                    $application
                        ->setOwmKey("");
                }


                $application->save();

                $payload = [
                    'success' => true,
                    'success_message' => __("Option successfully saved"),
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
}
