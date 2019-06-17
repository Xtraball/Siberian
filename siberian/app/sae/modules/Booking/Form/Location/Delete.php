<?php

/**
 * Class Booking_Form_Location_Delete
 */
class Booking_Form_Location_Delete extends Siberian_Form_Abstract
{
    /**
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path("/booking/application/delete-post"))
            ->setAttrib("id", "form-location-delete")
            ->setConfirmText(p__("booking", "You are about to remove this Location! Are you sure ?"));

        /** Bind as a delete form */
        self::addClass("delete", $this);

        $storeId = $this->addSimpleHidden("store_id");
        $storeId->setMinimalDecorator();

        $valueId = $this->addSimpleHidden("value_id");
        $valueId->setMinimalDecorator();

        $this->addMiniSubmit();
    }
}