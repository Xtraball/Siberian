<?php
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

        $display_search = $this->addSimpleCheckbox("display_search", __("Display Search"));
        $display_place_icon = $this->addSimpleCheckbox("display_place_icon", __("Display place icon"));
        $display_income = $this->addSimpleCheckbox("display_income", __("Display income"));

        $options = array(
            "hidden" => __("Hidden"),
            "contactform" => __("Contact form"),
            "email" => __("Email"),
            "both" => __("Email & Contact form"),
        );

        $display_contact = $this->addSimpleSelect("display_contact", __("Display contact"), $options);

        //$title_company = $this->addSimpleText("title_company", __("Alternate name for 'Company'"));
        //$title_place = $this->addSimpleText("title_place", __("Alternate name for 'Place'"));

        $distance = array(0 => 1, 1 => 5, 2 => 10, 3 => 20, 4 => 50, 5 => 75, 6 => 100, 7 => 150, 8 => 200, 9 => 500, 10 => 1000);
        $search_radius = $this->addSimpleSelect("default_radius", __("Default search distance"), $distance);

        $units = array(
            "km" => "Kilomètres",
            "mi" => "Miles"
        );
        $distance_unit = $this->addSimpleSelect("distance_unit", __("Distance unit"), $units);

        $value_id = $this->addSimpleHidden("value_id");
        $value_id
            ->setRequired(true)
        ;
    }
}