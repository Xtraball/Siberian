<?php

/**
 * Class Application_Form_Android
 */
class Application_Form_Android extends Siberian_Form_Abstract
{
    /**
     * @var string
     */
    public $color = 'color-green';

    /**
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path("application/customization_publication_infos/save-android"))
            ->setAttrib("id", "form-general-information-android");

        $this->setIsFormHorizontal(false);

        self::addClass("onchange", $this);

        $checkbox = $this->addSimpleCheckbox("disable_battery_optimization", __("Allows MediaPlayer (Audio & Radio) to ask permission for disabling <b>Android</b> battery optimizations."));
        $checkbox->addClass("floating-checkbox");

        $text1 = __("Please note that Google Play can reject your app if the use case is not strictly required.");
        $text2 = __("You can find acceptable use cases for permission <b>REQUEST_IGNORE_BATTERY_OPTIMIZATIONS</b> on Android %s, according to Android developers.",
            "<a class=\"link\" href=\"https://developer.android.com/training/monitoring-device-state/doze-standby#whitelisting-cases\">" . __("here") . "</a>");
        $batteryHelper = <<<RAW
<div class="col-md-12">
    {$text1}<br />
    {$text2}
</div>
RAW;
        $this->addSimpleHtml("html_helper_battery", $batteryHelper);


        $checkbox = $this->addSimpleCheckbox("disable_location", p__("application", "Disable location service & permissions."));
        $checkbox->addClass("floating-checkbox");

        $text3 = p__("application", "Please note that if you are using a feature that requires location services & disable it here, these features will not work correctly.");
        $locationHelper = <<<RAW
<div class="col-md-12">
    {$text3}
</div>
RAW;
        $this->addSimpleHtml("html_helper_location", $locationHelper);

        $checkbox = $this->addSimpleCheckbox("disable_media_permissions", p__("application", "Remove the photos & videos permissions (READ_MEDIA_IMAGES, READ_MEDIA_VIDEO)."));
        $checkbox->addClass("floating-checkbox");

        $text4 = p__("application", "Google Play only accepts these permissions for apps whose core purpose is photo or video access, and rejects the others. Picking a photo with the system photo picker keeps working without them.");
        $mediaHelper = <<<RAW
<div class="col-md-12">
    {$text4}
</div>
RAW;
        $this->addSimpleHtml("html_helper_media", $mediaHelper);
    }

    /**
     * @param $application
     * @return $this
     */
    public function fill($application)
    {
        $this
            ->getElement("disable_battery_optimization")
            ->setValue((boolean) $application->getDisableBatteryOptimization());

        $this
            ->getElement("disable_location")
            ->setValue((boolean) $application->getDisableLocation());

        $this
            ->getElement("disable_media_permissions")
            ->setValue((boolean) $application->getDisableMediaPermissions());

        return $this;
    }
}