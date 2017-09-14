<?php

class Cms_Form_Block_Button extends Cms_Form_Block_Abstract {

    /**
     * @var string
     */
    public $blockType = 'button';

    public function init() {
        parent::init();

        $this
            ->setAttrib("id", "form-cms-block-button-".$this->uniqid)
        ;

        # ICON
        $icon_fake = $this->addSimpleImage("icon_fake", __("Custom icon"), __("Custom icon"), array(
            "width" => 128,
            "height" => 128,
        ));

        $icon = $this->addSimpleHidden("icon");
        $icon->setBelongsTo("block[".$this->uniqid."][button]");
        $icon->addClass("cms-button-icon");

        # LABEL
        $label = $this->addSimpleText("label", __("Label"));
        $label->setBelongsTo("block[".$this->uniqid."][button]");
        $label->addClass("cms-button-label");

        # PHONE
        $phone = $this->addSimpleText("phone", __("Phone"));
        $phone->setBelongsTo("block[".$this->uniqid."][button]");
        $phone->addClass("cms-button-input cms-button-phone");

        # EMAIL
        $email = $this->addSimpleText("email", __("Email"));
        $email->setBelongsTo("block[".$this->uniqid."][button]");
        $email->addClass("cms-button-input cms-button-email");

        # LINK
        $link = $this->addSimpleText("link", __("Link"));
        $link->setBelongsTo("block[".$this->uniqid."][button]");
        $link->addClass("cms-button-input cms-button-link");

        # HIDE NAVBAR
        $link = $this->addSimpleCheckbox("hide_navbar", __("Hide navbar"));
        $link->setBelongsTo("block[".$this->uniqid."][button]");
        $link->addClass("cms-button-input cms-button-link");

        # USE EXTERNAL APP
        $link = $this->addSimpleCheckbox("use_external_app", __("Use external app"));
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

    /**
     * @param $block
     * @return $this
     */
    public function loadBlock($block) {

        $this->getElement("label")->setValue($block->getLabel());
        $this->getElement("type")->setValue($block->getTypeId());
        $this->getElement("hide_navbar")->setValue($block->getHideNavbar());
        $this->getElement("use_external_app")->setValue($block->getUseExternalApp());
        $this->getElement($block->getTypeId())->setValue($block->getContent());
        $this->getElement("icon")->setValue($block->getIcon());
        $this->getElement("icon_fake")->setValue($block->getIcon());

        return $this;
    }

}