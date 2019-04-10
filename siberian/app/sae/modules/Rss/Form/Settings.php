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

        $this->addSimpleSelect("aggregation", p__("rss","Feeds aggregation"), [
            "merge" => p__("rss", "Merge all feeds"),
            "split" => p__("rss", "Display feeds as groups"),
        ]);

        $this->addSimpleCheckbox("displayThumbnail", p__("rss","Display thumbnail"));
        $this->addSimpleCheckbox("displayCover", p__("rss","Use first entry as cover"));

        $this->addSimpleHtml("note_cache_refresh",
            "<div class=\"alert alert-info\">" .
            __("Cache is automatically cleared anytime a user hit refresh, whatever the lifetime set.") .
            "</div>",
            [
                "class" => "col-md-offset-3 col-md-7",
            ]
        );

        $cacheLifetime = $this->addSimpleSelect("cacheLifetime", p__("rss", "Cache lifetime (excluding user refresh)"), [
            "0" => __("No cache"),
            "60" => __("1 minute"),
            "900" => __("15 minutes"),
            "1800" => __("30 minutes"),
            "3600" => __("1 hour"),
            "10800" => __("3 hours"),
            "21600" => __("6 hours"),
            "43200" => __("12 hours"),
            "86400" => __("1 day"),
            "null" => __("Unlimited (until any user refresh)"),
        ]);

        $this->addSimpleHtml("note_no_cache",
            "<div class=\"alert alert-danger\">" .
            p__("rss", "Disabling cache can have significant performance impacts on aggregated feeds.") .
            "</div>",
            [
                "class" => "col-md-offset-3 col-md-7",
                "style" => "display: none;"
            ]
        );
        
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