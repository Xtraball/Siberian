<?php
/**
 * Class Job_Form_Company
 */
class Application_Form_Advanced extends Siberian_Form_Abstract {

    public function init() {
        parent::init();

        $this
            ->setAction(__path("/application/settings_advanced/save"))
            ->setAttrib("id", "form-application-advanced")
        ;

        /** Bind as a onchange form */
        self::addClass("onchange", $this);

        $offline_content = $this->addSimpleCheckbox("offline_content", __("Enable Offline content ?"));

        $google_maps_key = $this->addSimpleText("googlemaps_key", __("Google Maps JavaScript API Key"));
    }
}