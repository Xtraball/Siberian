<?php

class Cms_Form_Block_Address extends Cms_Form_Block_Abstract {

    /**
     * @var string
     */
    public $blockType = 'address';

    public function init() {
        parent::init();

        $this
            ->setAttrib("id", "form-cms-block-address-".$this->uniqid)
        ;

        $label = $this->addSimpleText("label", __("Label"));
        $label->setBelongsTo("block[".$this->uniqid."][address]");
        $label->setRequired(true);

        $address = $this->addSimpleTextarea("address", __("Address"));
        $address->setBelongsTo("block[".$this->uniqid."][address]");
        $address->addClass("cms-address");

        $latitude = $this->addSimpleText("latitude", __("Latitude"));
        $latitude->setBelongsTo("block[".$this->uniqid."][address]");
        $latitude->addClass("cms-latitude");

        $longitude = $this->addSimpleText("longitude", __("Longitude"));
        $longitude->setBelongsTo("block[".$this->uniqid."][address]");
        $longitude->addClass("cms-longitude");

        $show_address = $this->addSimpleCheckbox("show_address", __("Display address"));
        $show_address->setBelongsTo("block[".$this->uniqid."][address]");

        $show_geolocation_button = $this->addSimpleCheckbox("show_geolocation_button", __("Display location button"));
        $show_geolocation_button->setBelongsTo("block[".$this->uniqid."][address]");

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
        $this->getElement("address")->setValue($block->getAddress());
        $this->getElement("latitude")->setValue($block->getLatitude());
        $this->getElement("longitude")->setValue($block->getLongitude());
        $this->getElement("show_address")->setValue($block->getShowAddress());
        $this->getElement("show_geolocation_button")->setValue($block->getShowGeolocationButton());

        return $this;
    }

}