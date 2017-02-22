<?php

class Layouts_Form_Layout10Options extends Siberian_Form_Options_Abstract {


    public function init() {
        parent::init();

        /** Bind as a create form */
        self::addClass("create", $this);
        self::addClass("form-layout-options", $this);

        $shadow = $this->addSimpleSelect("shadow", __("Display Shadow"), array(
            "shadow" => __("Show"),
            "no-shadow" => __("Hidden"),
        ));

        $border = $this->addSimpleRadio("border", __("Display Border Circle"), array(
            "display" => __("Hidden"),
            "visible" => __("Visible"),
        ));

        $this->addNav("submit", __("Save"), false, false);

        self::addClass("btn-sm", $this->getDisplayGroup("submit")->getElement(__("Save")));

    }

}