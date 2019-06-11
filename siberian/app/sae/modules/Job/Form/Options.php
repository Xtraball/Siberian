<?php

use Job\Model\Currency;

/**
 * Class Job_Form_Company
 */
class Job_Form_Options extends Siberian_Form_Abstract {

    public function init() {
        parent::init();

        $this
            ->setAction(__path("/job/application/editoptionspost"))
            ->setAttrib("id", "form-options")
        ;

        /** Bind as a onchange form */
        self::addClass("onchange", $this);

        $display_place_icon = $this->addSimpleCheckbox("display_place_icon", p__("job", "Display place icon"));
        $display_income = $this->addSimpleCheckbox("display_income", p__("job", "Display income"));

        $options = [
            "hidden" => p__("job", "Hidden"),
            "contactform" => p__("job", "Contact form"),
            "email" => p__("job", "Email"),
            "both" => p__("job", "Email & Contact form"),
        ];

        $display_contact = $this->addSimpleSelect("display_contact", p__("job", "Display contact"), $options);

        //$title_company = $this->addSimpleText("title_company", __("Alternate name for 'Company'"));
        //$title_place = $this->addSimpleText("title_place", __("Alternate name for 'Place'"));

        $distance = [0 => 1, 1 => 5, 2 => 10, 3 => 20, 4 => 50, 5 => 75, 6 => 100, 7 => 150, 8 => 200, 9 => 500, 10 => 1000];
        $search_radius = $this->addSimpleSelect("default_radius", p__("job", "Default search distance"), $distance);

        $currency = $this->addSimpleSelect(
            "currency",
            p__("job", "Currency"),
            array_combine(Currency::$supported, Currency::$supported));

        $units = [
            "km" => p__("job", "Kilomètres"),
            "mi" => p__("job", "Miles")
        ];
        $distance_unit = $this->addSimpleSelect("distance_unit", p__("job", "Distance unit"), $units);

        $designs = [
            "list" => p__("job", "List"),
            "card" => p__("job", "Card")
        ];
        $cardDesign = $this->addSimpleSelect("card_design", p__("job", "Page design"), $designs);

        $value_id = $this->addSimpleHidden("value_id");
        $value_id
            ->setRequired(true)
        ;
    }
}