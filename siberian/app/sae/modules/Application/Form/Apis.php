<?php

/**
 * Class Application_Form_Apis
 */
class Application_Form_Apis extends Siberian_Form_Abstract {

    public function init() {
        parent::init();

        $something = false;

        $this
            ->setAction(__path("/application/settings_apis/save"))
            ->setAttrib("id", "form-application-apis")
        ;

        self::addClass("create", $this);

        if(Core_View_Default::_getAcl()->isAllowed("editor_settings_facebook")) {
            $facebook_id = $this->addSimpleText("facebook_id", __("App id"));
            $facebook_key = $this->addSimpleText("facebook_key", __("Secret Key"));
            $this->groupElements("facebook", array("facebook_id", "facebook_key"), __("Facebook API settings"));
            $something = true;
        }

        if(Core_View_Default::_getAcl()->isAllowed("editor_settings_twitter")) {
            $twitter_consumer_key = $this->addSimpleText("twitter_consumer_key", __("Twitter consumer key"));
            $twitter_consumer_secret = $this->addSimpleText("twitter_consumer_secret", __("Twitter consumer secret"));
            $twitter_api_token = $this->addSimpleText("twitter_api_token", __("Twitter API token"));
            $twitter_api_secret = $this->addSimpleText("twitter_api_secret", __("Twitter API secret"));
            $this->groupElements("twitter", array("twitter_consumer_key", "twitter_consumer_secret", "twitter_api_token", "twitter_api_secret"), __("Twitter API settings"));
            $something = true;
        }

        if(Core_View_Default::_getAcl()->isAllowed("editor_settings_instagram")) {
            $instagram_client_id = $this->addSimpleText("instagram_client_id", __("Client ID"));
            $instagram_token = $this->addSimpleText("instagram_token", __("Access Token"));
            $this->groupElements("instagram", array("instagram_client_id", "instagram_token"), __("Instagram API settings"));
            $something = true;
        }

        if(Core_View_Default::_getAcl()->isAllowed("editor_settings_flickr")) {
            $flickr_key = $this->addSimpleText("flickr_key", __("Flickr API key"));
            $flickr_secret = $this->addSimpleText("flickr_secret", __("Flickr API secret"));
            $this->groupElements("flickr", array("flickr_key", "flickr_secret"), __("Flickr API settings"));
            $something = true;
        }

        if($something) {
            $this->addNav("save", "Save", false);
        } else {
            $this->addSimpleHtml("warning", "<p>".__("Nothing to show for now.")."</p>", array(
                "class" => "col-md-12"
            ));
        }

    }
}