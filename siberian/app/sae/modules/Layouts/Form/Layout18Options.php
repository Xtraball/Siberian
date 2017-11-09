<?php

class Layouts_Form_Layout18Options extends Siberian_Form_Options_Abstract {


    public function init() {
        parent::init();

        /** Bind as a create form */
        self::addClass("create", $this);
        self::addClass("form-layout-options", $this);

        $label = $this->addSimpleSelect("label", __("Title position"), array(
            "label-left" => __("Left"),
            "label-right" => __("Right"),
        ));

        $textTransform = $this->addSimpleSelect("textTransform", __("Title case"), array(
            "title-lowcase" => __("Lower case"),
            "title-uppercase" => __("Upper case"),
        ));

        $borders = $this->addSimpleMultiCheckbox("borders", __("Display Borders"), array(
            "border-left" => __("Left"),
            "border-right" => __("Right"),
            "border-top" => __("Top"),
            "border-bottom" => __("Bottom"),
        ));

        $this->addNav("submit", __("Save"), false, false);

        self::addClass("btn-sm", $this->getDisplayGroup("submit")->getElement(__("Save")));

    }

}