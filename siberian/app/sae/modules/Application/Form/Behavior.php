<?php
/**
 * Class Application_Form_Behavior
 */
class Application_Form_Behavior extends Siberian_Form_Abstract {

    public $color = "color-red";

    public function init() {
        parent::init();

        $this
            ->setAction(__path("application/customization_design_style/behavior"))
            ->setAttrib("id", "form-behavior")
        ;

        /** Bind as a onchange form */
        self::addClass("onchange", $this);

        $background = $this->addSimpleCheckbox("use_homepage_background_image_in_subpages", __("Use the homepage image as background in all features"));
        $background->setCols("col-md-10 col-xs-10", "col-md-1 col-xs-1", "");
        $background->setNewDesign();

        $ios_status = $this->addSimpleCheckbox("ios_status_bar_is_hidden", __("Hide iOS status bar"));
        $ios_status->setCols("col-md-10 col-xs-10", "col-md-1 col-xs-1", "");
        $ios_status->setNewDesign();

        $iosStatus = '<span style="font-style: italic; font-weight: bold; font-size: 11px;">' .
            __('Changing this option requires you to republish your iOS application.') .
            '</span>';
        $this->addSimpleHtml('ios_status', $iosStatus,
            [
                'class' => 'col-md-10 col-xs-10',
                'style' => 'margin-top: -20px;'
            ]);

        $android_status = $this->addSimpleCheckbox(
            "android_status_bar_is_hidden",
            __("Hide Android status bar"));
        $android_status->setCols("col-md-10 col-xs-10", "col-md-1 col-xs-1", "");
        $android_status->setNewDesign();

        $androidStatus = '<span style="font-style: italic; font-weight: bold; font-size: 11px;">' .
            __('This option is not compatible with applications using Forms and/or features with Keyboard.') .
            '</span>';
        $this->addSimpleHtml('android_status', $androidStatus,
            [
                'class' => 'col-md-10 col-xs-10',
                'style' => 'margin-top: -20px;'
            ]);

    }
}