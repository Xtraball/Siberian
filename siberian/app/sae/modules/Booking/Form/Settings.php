<?php

/**
 * Class Booking_Form_Settings
 */
class Booking_Form_Settings extends Siberian_Form_Abstract
{

    /**
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path("/booking/application/edit-settings"))
            ->setAttrib("id", "form-booking-settings");

        /** Bind as a create form */
        self::addClass("create", $this);

        $coverTitle = p__("booking", "Cover");
        $this->addSimpleImage("cover", $coverTitle, $coverTitle, [
            "width" => 1000,
            "height" => 640,
        ]);

        $this->addSimpleTextarea("description", p__("booking", "Description"));

        $this->addSimpleSelect("datepicker", p__("booking","Dates"), [
            "single" => p__("booking", "Single date"),
            "checkin" => p__("booking", "Checkin & Checkout"),
        ]);

        $this->addSimpleSelect("design", p__("booking","Design"), [
            "list" => p__("booking", "List"),
            "card" => p__("booking", "Card"),
        ]);

        $this->addSimpleSelect("date_format", p__("booking","Date format"), [
            "MM/DD/YYYY HH:mm" => "MM/DD/YYYY HH:mm",
            "DD/MM/YYYY HH:mm" => "DD/MM/YYYY HH:mm",
            "MM DD YYYY HH:mm" => "MM DD YYYY HH:mm",
            "DD MM YYYY HH:mm" => "DD MM YYYY HH:mm",
            "YYYY-MM-DD HH:mm" => "YYYY-MM-DD HH:mm",
            "YYYY MM DD HH:mm" => "YYYY MM DD HH:mm",
        ]);
        
        $value_id = $this->addSimpleHidden("value_id");
        $value_id
            ->setRequired(true);

        $submit = $this->addSubmit(p__("booking", "Save"));
        $submit->addClass("pull-right");
    }
}