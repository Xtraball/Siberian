<?php

class Layouts_Form_Layout1Options extends Siberian_Form_Options_Abstract {


    public function init() {
        parent::init();

        /** Bind as a create form */
        self::addClass("create", $this);
        self::addClass("form-layout-options", $this);
-
        $shadow = $this->addSimpleSelect("shadow", __("Border shadow"), array(
            "shadow" => __("Visible"),
            "no-shadow" => __("Hidden"),
        ));

        $this->addNav("submit", __("Save"), false, false);

        self::addClass("btn-sm", $this->getDisplayGroup("submit")->getElement(__("Save")));

    }

}