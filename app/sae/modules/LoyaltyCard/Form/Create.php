<?php

class LoyaltyCard_Form_Create extends Siberian_Form_Abstract {

    public function init() {
        parent::init();

        $this
            ->setAction(__path("/loyaltycard/application/editpost"))
            ->setAttrib("id", "form-loyaltycard")
            ->addNav("loyaltycard-nav")
        ;

        /** Bind as a create form */
        self::addClass("create", $this);

        $this->addSimpleHidden("card_id");

        $name = $this->addSimpleText("name", __("Card name"));
        $name
            ->setRequired(true)
        ;

        $number_of_points = $this->addSimpleSelect("number_of_points", __("Number of points"), range(0,20));
        $number_of_points
            ->setRequired(true)
        ;

        $reward = $this->addSimpleText("advantage", __("Reward"));
        $reward
            ->setRequired(true)
        ;

        $conditions = $this->addSimpleText("conditions", __("1 point"));
        $conditions
            ->setRequired(true)
        ;

        $use_only_once = $this->addSimpleCheckbox("use_once", __("Use only once?"));

        $image_inactive = $this->addSimpleImage("image_inactive", __("Point inactive"), __("Point inactive"), array("width" => 256, "height" => 256), array(), true);
        $image_inactive
            ->addClass("default_button")
            ->addClass("form_button");

        $image_active = $this->addSimpleImage("image_active", __("Point active"), __("Point active"), array("width" => 256, "height" => 256), array(), true);
        $image_active
            ->addClass("default_button")
            ->addClass("form_button");

        $this->addNav("repeat", "OK", false, true);

        $value_id = $this->addSimpleHidden("value_id");
        $value_id
            ->setRequired(true)
        ;
    }
}