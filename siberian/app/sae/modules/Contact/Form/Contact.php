<?php

/**
 * Class Contact_Form_Contact
 */
class Contact_Form_Contact extends Siberian_Form_Abstract
{

    /**
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path("/contact/application/edit-post"))
            ->setAttrib("id", "form-contact");

        /** Bind as a create form */
        self::addClass("create", $this);

        $this->addSimpleHidden("contact_id");

        $name = $this->addSimpleText("name", p__("contact","Name"));
        $name
            ->setRequired(true);

        $this->addSimpleImage(
            "cover",
            p__("contact","Cover image"),
            p__("contact","Cover image"),
            [
                "width" => 1080,
                "height" => 600,
                "required" => false
            ]
        );

        $address = $this->addSimpleTextarea("address", p__("contact","Address"));
        $address
            ->setRequired(true);

        $displayLocateAction = $this->addSimpleCheckbox('display_locate_action',
            p__('contact', 'Display locate action'));

        $description = $this->addSimpleTextarea("description", p__("contact","Description"));
        $description
            ->setRequired(true)
            ->setRichtext(false);

        $this->addSimpleText("phone", p__("contact","Phone"));
        $this->addSimpleText("email", p__("contact","E-mail"));
        $this->addSimpleText("website", p__("contact","Website"));
        $this->addSimpleText("facebook", p__("contact","Facebook"));
        $this->addSimpleText("twitter", p__("contact","Twitter"));

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
