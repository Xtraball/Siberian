<?php

/**
 * Class Rss_Form_Settings
 */
class Rss_Form_Settings extends Siberian_Form_Abstract
{

    /**
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path("/rss/application/edit-settings"))
            ->setAttrib("id", "form-rss-settings");

        /** Bind as a create form */
        self::addClass("create", $this);


        $this->addSimpleSelect("design", p__("rss","Design"), [
            "card" => p__("rss", "Card"),
            "list" => p__("rss", "List"),
        ]);

        $cardDesignHint = p__("rss", "Card & list only applies to layout 1");
        $html = <<<RAW
<div class="alert alert-warning">
    $cardDesignHint
</div>
RAW;

        $this->addSimpleHtml("card_design_hint", $html, [
            "class" => "col-sm-7 col-sm-offset-3"
        ]);

        $this->addSimpleSelect("aggregation", p__("rss","Feeds aggregation"), [
            "merge" => p__("rss", "Merge all feeds (defaults)"),
            "split" => p__("rss", ""),
        ]);

        $this->addSimpleCheckbox("displayThumbnail", p__("rss","Display thumbnail"));
        $this->addSimpleCheckbox("displayCover", p__("rss","Display cover image"));

        $value_id = $this->addSimpleHidden("value_id");
        $value_id
            ->setRequired(true);

        $submit = $this->addSubmit(p__("rss", "Save"));
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