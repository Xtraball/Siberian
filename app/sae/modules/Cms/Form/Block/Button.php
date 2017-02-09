<?php

class Cms_Form_Block_Button extends Cms_Form_Block_Abstract {

    public function init() {
        parent::init();

        $this
            ->setAttrib("id", "form-cms-block-button-".$this->uniqid)
        ;

        # PHONE
        $phone = $this->addSimpleText("phone", __("Phone"));
        $phone->setBelongsTo("block[".$this->uniqid."][button]");
        $phone->addClass("cms-button-input cms-button-phone");


        # EMAIL
        $email = $this->addSimpleText("email", __("Email"));
        $email->setBelongsTo("block[".$this->uniqid."][button]");
        $email->addClass("cms-button-input cms-button-email");


        # LINK
        $link_label = $this->addSimpleText("label", __("Label"));
        $link_label->setBelongsTo("block[".$this->uniqid."][button]");
        $link_label->addClass("cms-button-input cms-button-link");

        $link = $this->addSimpleText("link", __("Link"));
        $link->setBelongsTo("block[".$this->uniqid."][button]");
        $link->addClass("cms-button-input cms-button-link");

        # BUTTON TYPE
        $type = $this->addSimpleHidden("type");
        $type->setBelongsTo("block[".$this->uniqid."][button]");
        $type->addClass("cms-button-input cms-input-button-type");


        # VALUE ID
        $value_id = $this->addSimpleHidden("value_id");
        $value_id
            ->setRequired(true)
        ;
    }

}