<?php

class Layouts_Form_ApartmentsOptions extends Siberian_Form_Options_Abstract {


    public function init() {
        parent::init();

        /** Bind as a create form */
        self::addClass("create", $this);
        self::addClass("form-layout-options", $this);

        $icons = $this->addSimpleSelect("icons", __("Icons & Images"), array(
            "default" => __("Square icons"),
            "cover" => __("Cover background images"),
        ));

        $this->addSimpleCheckbox("visible", __("Display titles"));

        $this->addNav("submit", __("Save"), false, false);

        self::addClass("btn-sm", $this->getDisplayGroup("submit")->getElement(__("Save")));

    }

}