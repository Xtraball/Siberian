<?php
/**
 * Class Job_Form_Company
 */
class Application_Form_Twitter extends Siberian_Form_Abstract {

    public function init() {
        parent::init();

        $this
            ->setAction(__path("/application/settings_twitter/save"))
            ->setAttrib("id", "form-application-twitter")
        ;

        self::addClass("create", $this);

        $twitter_consumer_key = $this->addSimpleText("twitter_consumer_key", __("Twitter consumer key"));
        $twitter_consumer_secret = $this->addSimpleText("twitter_consumer_secret", __("Twitter consumer secret"));
        $twitter_api_token = $this->addSimpleText("twitter_api_token", __("Twitter API token"));
        $twitter_api_secret = $this->addSimpleText("twitter_api_secret", __("Twitter API secret"));

        $this->addNav("save", "Save", false);
    }
}