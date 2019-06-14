<?php

/**
 * Class Booking_Form_Location
 */
class Booking_Form_Location extends Siberian_Form_Abstract
{
    /**
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path("/booking/application/edit-post"))
            ->setAttrib("id", "form-booking-settings")
            ->addNav("booking-location-nav");

        /** Bind as a create form */
        self::addClass("create", $this);

        $storeName = $this->addSimpleText("store_name", p__("booking", "Location name"));
        $storeName->setRequired(true);

        $email = $this->addSimpleText("email", p__("booking", "E-mail"));
        $email->setRequired(true);

        $this->addSimpleHidden("booking_id");
        $this->addSimpleHidden("store_id");

        $value_id = $this->addSimpleHidden("value_id");
        $value_id
            ->setRequired(true);
    }

    /**
     * @param $storeId
     * @return $this
     */
    public function setStoreId ($storeId)
    {
        $this->getElement("store_id")->setValue($storeId);

        return $this;
    }
}