<?php

class Places_Form_Search extends Siberian_Form_Abstract {

    public function init() {
        parent::init();

        $this
            ->setAction(__path("/cms/application_page/editpostv2"))
            ->setAttrib("id", "form-cms")
        ;

        /** Text */
        $show_text = $this->addSimpleCheckbox("show", __("Text"));
        $show_text->setBelongsTo("search[text]");

        $label_text = $this->addSimpleText("label", __("Label"));
        $label_text->setBelongsTo("search[text]");

        /** Type */
        $show_type = $this->addSimpleCheckbox("show", __("Type"));
        $show_type->setBelongsTo("search[type]");

        $label_type = $this->addSimpleText("label", __("Label"));
        $label_type->setBelongsTo("search[type]");

        /** Address */
        $show_address = $this->addSimpleCheckbox("show", __("Type"));
        $show_address->setBelongsTo("search[type]");

        $label_address = $this->addSimpleText("label", __("Label"));
        $label_address->setBelongsTo("search[type]");
    }
}