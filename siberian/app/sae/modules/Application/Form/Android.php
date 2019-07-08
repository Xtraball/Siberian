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

        return $this;
    }
}