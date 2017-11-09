<?php
/**
 * Class Application_Form_Layout
 */
class Application_Form_Layout extends Siberian_Form_Abstract {

    public function init() {
        parent::init();

        $this
            ->setAction(__path("application/customization_design_style/updetalayout"))
            ->setAttrib("id", "form-layout-advanced")
        ;

        /** Bind as a onchange form */
        self::addClass("onchange", $this);

        $offline_content = $this->addSimpleCheckbox("offline_content", __("Enable Offline content ?"));

        $google_maps_key = $this->addSimpleText("googlemaps_key", __("Google Maps JavaScript API Key"));
    }
}