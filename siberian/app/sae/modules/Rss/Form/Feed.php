<?php

/**
 * Class Rss_Form_Feed
 */
class Rss_Form_Feed extends Siberian_Form_Abstract
{

    /**
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path("/rss/application/edit-post"))
            ->setAttrib("id", "form-rss");

        /** Bind as a create form */
        self::addClass("create", $this);

        $this->addSimpleHidden("feed_id");

        $title = $this->addSimpleText("title", p__("rss","Title"));
        $title
            ->setRequired(true);

        $subtitle = $this->addSimpleTextarea("subtitle", p__("rss","Description / Subtitle"));

        $link = $this->addSimpleText("link", p__("rss","Feed URL (RSS)"));
        $link
            ->setRequired(true);

        $thumbnailTitle = p__("rss", "Add a thumbnail");
        $this->addSimpleImage("thumbnail", $thumbnailTitle, $thumbnailTitle, [
            "width" => 256,
            "height" => 256,
        ]);

        $value_id = $this->addSimpleHidden("value_id");
        $value_id
            ->setRequired(true);

        $submit = $this->addSubmit(p__("rss", "Save"));
        $submit->addClass("pull-right");
    }

    /**
     * @param $feedId
     */
    public function setFeedId($feedId)
    {
        $this
            ->getElement("feed_id")
            ->setValue($feedId)
            ->setRequired(true);
    }
}