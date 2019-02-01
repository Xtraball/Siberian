<?php

/**
 * Class Application_Form_Apis
 */
class Application_Form_Apis extends Siberian_Form_Abstract
{
    /**
     * @var string
     */
    private $callbackUri;

    /**
     * @param string $callbackUri
     */
    public function setLoginCallbackUri ($callbackUri)
    {
        $this->callbackUri = $callbackUri;
    }

    /**
     * @throws Zend_Form_Exception
     */
    public function localInit()
    {
        $something = false;

        $this
            ->setAction(__path("/application/settings_apis/save"))
            ->setAttrib("id", "form-application-apis");

        $acl = Core_View_Default::_sGetAcl();

        self::addClass("create", $this);

        if ($acl->isAllowed("editor_settings_facebook")) {
            $this->addSimpleHtml(
                'hint_callback_uri',
                '<div class="col-md-3">' . __('Callback URIs')
                . '</div><div class="col-md-7"><p style="letter-spacing: 1px;">' .  $this->callbackUri . ' https://localhost/callback</p></div>');
            $this->addSimpleText("facebook_id", __("App id"));
            $this->addSimpleText("facebook_key", __("Secret Key"));
            $this->groupElements(
                "facebook",
                [
                    "hint_callback_uri",
                    "facebook_id",
                    "facebook_key"
                ],
                __("Facebook API settings"));
            $something = true;
        }

        if ($acl->isAllowed("editor_settings_twitter")) {
            $this->addSimpleText("twitter_consumer_key", __("Twitter consumer key"));
            $this->addSimpleText("twitter_consumer_secret", __("Twitter consumer secret"));
            $this->addSimpleText("twitter_api_token", __("Twitter API token"));
            $this->addSimpleText("twitter_api_secret", __("Twitter API secret"));
            $this->groupElements(
                "twitter",
                [
                    "twitter_consumer_key",
                    "twitter_consumer_secret",
                    "twitter_api_token",
                    "twitter_api_secret"
                ],
                __("Twitter API settings"));
            $something = true;
        }

        if ($acl->isAllowed("editor_settings_instagram")) {
            $this->addSimpleText("instagram_client_id", __("Client ID"));
            $this->addSimpleText("instagram_token", __("Access Token"));
            $this->groupElements(
                "instagram",
                [
                    "instagram_client_id",
                    "instagram_token"
                ],
                __("Instagram API settings"));
            $something = true;
        }

        if ($acl->isAllowed("editor_settings_flickr")) {
            $this->addSimpleText("flickr_key", __("Flickr API key"));
            $this->addSimpleText("flickr_secret", __("Flickr API secret"));
            $this->groupElements(
                "flickr",
                [
                    "flickr_key",
                    "flickr_secret"
                ],
                __("Flickr API settings"));
            $something = true;
        }

        $google_maps_key = $this->addSimpleText("googlemaps_key", __("Google Maps JavaScript API Key"));
        $this->groupElements(
            "google_maps",
            [
                "googlemaps_key"
            ],
            __("Google Maps settings"));

        $openWeatherMap = $this->addSimpleText("openweathermap_key", __("OpenWeatherMap API Key"));
        $this->groupElements(
            "openweathermap",
            [
                "openweathermap_key"
            ],
            __("OpenWeatherMap settings"));
        $something = true;

        if ($something) {
            $this->addNav("save", "Save", false);
        } else {
            $this->addSimpleHtml(
                "warning",
                "<p>" . __("Nothing to show for now.") . "</p>",
                [
                    "class" => "col-md-12"
                ]);
        }

    }
}