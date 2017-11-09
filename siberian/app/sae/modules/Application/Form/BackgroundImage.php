<?php
/**
 * Class Application_Form_BackgroundImage
 */
class Application_Form_BackgroundImage extends Siberian_Form_Abstract {

    public function init() {
        parent::init();

        $this
            ->setAction(__path("/application/customization_features_edit_background/save"))
            ->setAttrib("id", "form-application-background")
        ;

        // Bind as a onchange form!
        self::addClass("create", $this);

        $background_image = $this->addSimpleImage(
            "background_image",
            __("Background (portrait)"),
            __("Background (portrait)"),
            array(
                "width" => 1080,
                "height" => 1920
            ),
            array(),
            true
        );

        $background_landscape_image = $this->addSimpleImage(
            "background_landscape_image",
            __("Background (landscape)"),
            __("Background (landscape)"),
            array(
                "width" => 1920,
                "height" => 1080
            ),
            array(),
            true
        );

        $this->addSubmit(__("Save"))
            ->addClass('pull-right');

        $value_id = $this->addSimpleHidden("value_id");
        $value_id
            ->setRequired(true)
        ;
    }
}