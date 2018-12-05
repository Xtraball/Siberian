<?php

/**
 * Class Cms_Form_Block_Address
 */
class Cms_Form_Block_Address extends Cms_Form_Block_Abstract
{

    /**
     * @var string
     */
    public $blockType = 'address';

    /**
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAttrib("id", "form-cms-block-address-" . $this->uniqid);

        $label = $this->addSimpleText("label", __("Label"));
        $label->setBelongsTo("block[" . $this->uniqid . "][address]");
        $label->setRequired(true);

        $address = $this->addSimpleTextarea("address", __("Address"));
        $address->setBelongsTo("block[" . $this->uniqid . "][address]");
        $address->addClass("cms-address");

        $latitude = $this->addSimpleText("latitude", __("Latitude"));
        $latitude->setBelongsTo("block[" . $this->uniqid . "][address]");
        $latitude->addClass("cms-latitude");

        $longitude = $this->addSimpleText("longitude", __("Longitude"));
        $longitude->setBelongsTo("block[" . $this->uniqid . "][address]");
        $longitude->addClass("cms-longitude");

        $phone = $this->addSimpleText("phone", __("Phone number"));
        $phone->setBelongsTo("block[" . $this->uniqid . "][address]");
        $phone->addClass("cms-phone");

        $show_phone = $this->addSimpleCheckbox("show_phone", __("Display phone"));
        $show_phone->setBelongsTo("block[" . $this->uniqid . "][address]");

        $website = $this->addSimpleText("website", __("Website"));
        $website->setBelongsTo("block[" . $this->uniqid . "][address]");
        $website->addClass("cms-website");

        $show_website = $this->addSimpleCheckbox("show_website", __("Display website"));
        $show_website->setBelongsTo("block[" . $this->uniqid . "][address]");

        $show_address = $this->addSimpleCheckbox("show_address", __("Display address"));
        $show_address->setBelongsTo("block[" . $this->uniqid . "][address]");

        $show_geolocation_button = $this->addSimpleCheckbox("show_geolocation_button", __("Display location button"));
        $show_geolocation_button->setBelongsTo("block[" . $this->uniqid . "][address]");

        $value_id = $this->addSimpleHidden("value_id");
        $value_id
            ->setRequired(true);
    }

    /**
     * @param $block
     * @return $this
     */
    public function loadBlock($block)
    {
        $this->getElement("label")->setValue($block->getLabel());
        $this->getElement("address")->setValue($block->getAddress());
        $this->getElement("latitude")->setValue($block->getLatitude());
        $this->getElement("longitude")->setValue($block->getLongitude());
        $this->getElement("phone")->setValue($block->getPhone());
        $this->getElement("website")->setValue($block->getWebsite());
        $this->getElement("show_phone")->setValue($block->getShowPhone());
        $this->getElement("show_website")->setValue($block->getShowWebsite());
        $this->getElement("show_address")->setValue($block->getShowAddress());
        $this->getElement("show_geolocation_button")->setValue($block->getShowGeolocationButton());

        return $this;
    }
}