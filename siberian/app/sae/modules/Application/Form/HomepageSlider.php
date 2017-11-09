<?php
/**
 * Class Application_Form_Layout
 */
class Application_Form_HomepageSlider extends Siberian_Form_Abstract {

    public $color = "color-red";

    public function init() {
        parent::init();

        $this
            ->setAction(__path("application/customization_design_style/homepageslider"))
            ->setAttrib("id", "form-homepageslider")
        ;

        /** Bind as a onchange form */
        self::addClass("onchange", $this);

        $loop = $this->addSimpleCheckbox("homepage_slider_loop_at_beginning", __("Loop over images"));

        $duration = $this->addSimpleSlider("homepage_slider_duration", __("Slideshow duration"), array(
            "min" => 1,
            "max" => 100,
            "step" => 1,
            "unit" => " ".__("seconds"),
        ), true);

        $top = $this->addSimpleSlider("homepage_slider_offset", __("Top position"), array(
            "min" => 0,
            "max" => 100,
            "step" => 1,
            "unit" => "%",
        ), true);

        $opacity = $this->addSimpleSlider("homepage_slider_opacity", __("Images opacity"), array(
            "min" => 0,
            "max" => 100,
            "step" => 1,
            "unit" => "%",
        ), true);

        $height = $this->addSimpleSlider("homepage_slider_size", __("Images height"), array(
            "min" => 0,
            "max" => 100,
            "step" => 1,
            "unit" => "%",
        ), true);

        $this->groupElements("homepage_slider", array("homepage_slider_loop_at_beginning", "homepage_slider_duration", "homepage_slider_offset", "homepage_slider_opacity", "homepage_slider_size"), __("Homepage slider options"));
    }
}