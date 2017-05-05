<?php
/**
 * Class Application_Form_Customization_Publication_App
 */
class Application_Form_Customization_Publication_App extends Siberian_Form_Abstract {

    public $color = "color-purple";

    public function init() {

        parent::init();

        $this
            ->setAction(__path("/application/customization_publication_app/iconspost"))
            ->setAttrib("id", "form-application-appicons")
        ;

        /** Bind as a onchange form */
        self::addClass("onchange", $this);

    }

    /**
     *
     */
    public function setAppIcon() {
        $application_icon = $this->addSimpleImage("icon", __("Application icon"), __("Application icon"), array(
            "width" => 256,
            "height" => 256,
            "required" => true,
        ));
        $application_icon->setRequired(true);
    }

    /**
     *
     */
    public function setAndroidSettings() {
        self::removeClass("onchange", $this);
        self::addClass("create", $this);

        $this->setAttrib("id", "form-application-androidsettings");

        $notice_republish = '
<div>
    <div class="col-md-12">
        '.__("If you want to change the push icon, you <b>must re-publish</b> the application").'
        <br />
        <span style="font-size: 12px;">'.__("<b>Note: </b>Icon must be white with transparency, the crop window will colorize it otherwise").'</span>
    </div>
</div>';

        $this->addSimpleHtml("notice_republish", $notice_republish);

        $android_push_icon = $this->addSimpleImage("android_push_icon", __("Upload icon"), __("Upload icon"), array(
            "width" => 128,
            "height" => 128,
            "required" => true,
            "data-imagecolor" => "#FFFFFF",
            "data-forcecolor" => true
        ));
        $android_push_icon->setRequired(true);

        $notice_icons = '
<div>
    <div class="col-md-12">
        '.__("<b>Push image & Color</b> are dynamic and doesn't require the app to be re-published.").'
    </div>
</div>';

        $this->addSimpleHtml("notice_icons", $notice_icons);

        $android_push_image = $this->addSimpleImage("android_push_image", __("Upload image"), __("Upload image"), array(
            "width" => 512,
            "height" => 512,
        ));

        $color_html = '
<div>
    <div class="col-md-12">
        <div class="android-colorlabel"><b>'.__("Color").': </b></div>
        <div class="android-colorpicker-block">
            <input type="text" class="android-colorpicker-input input-flat" name="android_push_color" id="android_push_color" value="" />
            <div class="android-colorpicker"></div>
        </div>
       
    </div>
</div>';

        $this->addSimpleHtml("colorpicker", $color_html);

        $this->addNav("save", __("Save"), false);
    }
}