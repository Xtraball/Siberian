<?php

class Layouts_Form_Layout6Options extends Siberian_Form_Options_Abstract {


    public function init() {
        parent::init();

        /** Bind as a create form */
        self::addClass("create", $this);
        self::addClass("form-layout-options", $this);
-
        $label = $this->addSimpleSelect("label", __("Title position"), array(
            "label-left" => __("Left position"),
            "label-right" => __("Right position"),
        ));

        $textTransform = $this->addSimpleSelect("textTransform", __("Title case"), array(
            "title-lowcase" => __("Lower case"),
            "title-uppercase" => __("Upper case"),
        ));

        $this->addNav("submit", __("Save"), false, false);

        self::addClass("btn-sm", $this->getDisplayGroup("submit")->getElement(__("Save")));

    }

}