<?php

/**
 * Class Contact_Form_Design
 */
class Contact_Form_Design extends Siberian_Form_Abstract
{

    /**
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path("/contact/application/edit-design"))
            ->setAttrib("id", "form-contact-design");

        /** Bind as a create form */
        self::addClass("create", $this);

        $this->addSimpleHidden("contact_id");



        $this->addSimpleSelect("design", p__("contact","Design"), [
            "card" => p__("contact", "Card"),
            "list" => p__("contact", "List"),
        ]);

        $value_id = $this->addSimpleHidden("value_id");
        $value_id
            ->setRequired(true);

        $submit = $this->addSubmit(p__("contact", "Save"));
        $submit->addClass("pull-right");
    }

    /**
     * @param $contactId
     */
    public function setContactId($contactId)
    {
        $this
            ->getElement("contact_id")
            ->setValue($contactId)
            ->setRequired(true);
    }
}