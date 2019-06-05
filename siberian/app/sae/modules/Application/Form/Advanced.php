<?php

/**
 * Class Application_Form_Advanced
 */
class Application_Form_Advanced extends Siberian_Form_Abstract
{
    /**
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path("/application/settings_advanced/save"))
            ->setAttrib("id", "form-application-advanced");

        // Bind as a onchange form!
        self::addClass("onchange", $this);

        $offline_content = $this->addSimpleCheckbox("offline_content", __("Enable Offline content ?"));

        $fidelity_rate = $this->addSimpleNumber("fidelity_rate", __("Fidelity points rate"), 0, 10000, true, 0.01);

        $this->groupElements("fidelity", ["fidelity_rate"], __("Fidelity points"));
    }
}