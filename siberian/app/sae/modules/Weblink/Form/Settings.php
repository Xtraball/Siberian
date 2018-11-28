<?php

/**
 * Class Weblink_Form_Settings
 */
class Weblink_Form_Settings extends Siberian_Form_Abstract
{
    /**
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path("/weblink/application/edit-settings"))
            ->setAttrib("id", "form-edit-settings");

        self::addClass('onchange', $this);

        $this->addSimpleHidden("value_id");

        $this->addSimpleCheckbox("showSearch", __("Enable search"));
        $this->addSimpleSelect("cardDesign", __("Page design"), [
            "0" => __("List"),
            "1" => __("Card"),
        ]);
    }
}