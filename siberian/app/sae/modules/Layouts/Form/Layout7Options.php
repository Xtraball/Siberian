<?php

class Layouts_Form_Layout7Options extends Siberian_Form_Options_Abstract {


    public function init() {
        parent::init();

        /** Bind as a create form */
        self::addClass("create", $this);
        self::addClass("form-layout-options", $this);

        $title = $this->addSimpleRadio("title", __("Display titles"), array(
            "titlevisible" => __("Visible"),
            "titlehidden" => __("Hidden"),
        ));

        $textTransform = $this->addSimpleSelect("textTransform", __("Title case"), array(
            "title-lowcase" => __("Lower case"),
            "title-uppercase" => __("Upper case"),
        ));

        $borders = $this->addSimpleMultiCheckbox("borders", __("Display borders"), array(
            "border-right" => __("Right"),
            "border-bottom" => __("Bottom"),
        ));

        $this->addNav("submit", __("Save"), false, false);

        self::addClass("btn-sm", $this->getDisplayGroup("submit")->getElement(__("Save")));

    }

}