<?php

class Radio_Form_Radio extends Siberian_Form_Abstract {

    public function init() {
        parent::init();

        $this
            ->setAction(__path("/radio/application/editpost"))
            ->setAttrib("id", "form-radio")
        ;

        /** Bind as a create form */
        self::addClass("create", $this);

        $this->addSimpleHidden("radio_id");

        $title = $this->addSimpleText("title", __("Title:"));
        $title
            ->setRequired(true)
        ;

        $link = $this->addSimpleText("link", __("Link:"));
        $link
            ->setRequired(true)
        ;

        $background = $this->addSimpleImage("background", __("Background"), __("Import a background image"), array("width" => 1080, "height" => 1920), array(), true);
        $background
            ->addClass("default_button")
            ->addClass("form_button");

        $this->addSubmit(__("Save"))
            ->addClass("default_button")
            ->addClass("submit_button");

        $value_id = $this->addSimpleHidden("value_id");
        $value_id
            ->setRequired(true)
        ;
    }

    public function setRadioId($radio_id) {
        $this->getElement("radio_id")->setValue($radio_id)->setRequired(true);
    }
}