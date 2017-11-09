<?php

class Layouts_Form_Layout4Options extends Siberian_Form_Options_Abstract {


    public function init() {
        parent::init();

        /** Bind as a create form */
        self::addClass("create", $this);
        self::addClass("form-layout-options", $this);

        $title = $this->addSimpleRadio("title", __("Titles"), array(
            "titlevisible" => __("Visible"),
            "titlehidden" => __("Hidden"),
        ));


        $this->addNav("submit", __("Save"), false, false);

        self::addClass("btn-sm", $this->getDisplayGroup("submit")->getElement(__("Save")));

    }

}