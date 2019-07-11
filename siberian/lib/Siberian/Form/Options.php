<?php

/**
 * Class Siberian_Form_Options
 */
class Siberian_Form_Options extends Siberian_Form_Options_Abstract
{
    /**
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        self::addClass("create", $this);

        $this
            ->setAction(__path("/application/customization_design_style/formoptions"))
            ->setAttrib("id", "form-options-simple");

        if ($this->layout->getVisibility() == Application_Model_Layout_Homepage::VISIBILITY_ALWAYS) {
            $this->addSimpleCheckbox("layout_visibility", __("Visible from all pages"));
        }

        if ($this->layout->getUseHomepageSlider()) {
            $this->addSimpleCheckbox("homepage_slider_is_visible", __("Display the homepage slider"));
        }

        $homepageoptions = $this->addSimpleHidden("homepageoptions");
        $homepageoptions->setValue(true);

        $submit = $this->addSubmit(__("Save"));
        $submit->addClass("pull-right btn-sm");
    }

    /**
     * @param array $values
     * @return Zend_Form
     */
    public function populate(array $values)
    {
        if (isset($values["layout_visibility"]) && ($values["layout_visibility"] == Application_Model_Layout_Homepage::VISIBILITY_ALWAYS)) {
            $this->removeElement("homepage_slider_is_visible");
            $values["layout_visibility"] = "1";
        }

        return parent::populate($values);
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->getElement("layout_visibility")) &&
            empty($this->getElement("homepage_slider_is_visible"));
    }
}